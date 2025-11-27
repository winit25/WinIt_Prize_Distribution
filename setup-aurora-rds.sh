#!/bin/bash

# Aurora RDS Setup Script for Buypower
# This script creates an Aurora MySQL cluster for the Buypower application

set -e  # Exit on error

# Configuration
REGION="us-east-1"
CLUSTER_ID="buypower-aurora-cluster"
INSTANCE_ID="buypower-aurora-instance-1"
DB_NAME="buypower_db"
MASTER_USER="admin"
SUBNET_GROUP="buypower-aurora-subnet"
SG_NAME="buypower-aurora-sg"
INSTANCE_CLASS="db.t3.small"  # ~$40/month for dev/test

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Buypower Aurora RDS Setup${NC}"
echo -e "${GREEN}========================================${NC}\n"

# Check if Aurora cluster already exists
echo -e "${YELLOW}Checking for existing Aurora cluster...${NC}"
EXISTING_CLUSTER=$(aws rds describe-db-clusters \
  --db-cluster-identifier $CLUSTER_ID \
  --region $REGION 2>&1 || echo "")

if echo "$EXISTING_CLUSTER" | grep -q "DBClusterIdentifier"; then
  echo -e "${GREEN}✓ Aurora cluster '$CLUSTER_ID' already exists${NC}"
  
  # Get endpoint
  ENDPOINT=$(aws rds describe-db-clusters \
    --db-cluster-identifier $CLUSTER_ID \
    --region $REGION \
    --query 'DBClusters[0].Endpoint' \
    --output text)
  
  echo -e "${GREEN}Endpoint: $ENDPOINT${NC}"
  echo -e "\nSkipping cluster creation. Use this endpoint in your environment variables."
  echo -e "\nTo configure EB environment, run:"
  echo -e "${YELLOW}eb setenv DB_HOST=$ENDPOINT DB_DATABASE=$DB_NAME${NC}\n"
  exit 0
fi

# Prompt for master password
echo -e "${YELLOW}Enter a strong master password for Aurora RDS:${NC}"
read -s -p "Password: " MASTER_PASSWORD
echo ""
read -s -p "Confirm password: " MASTER_PASSWORD_CONFIRM
echo ""

if [ "$MASTER_PASSWORD" != "$MASTER_PASSWORD_CONFIRM" ]; then
  echo -e "${RED}✗ Passwords do not match${NC}"
  exit 1
fi

if [ ${#MASTER_PASSWORD} -lt 8 ]; then
  echo -e "${RED}✗ Password must be at least 8 characters${NC}"
  exit 1
fi

echo -e "${GREEN}✓ Password confirmed${NC}\n"

# Get default VPC
echo -e "${YELLOW}Getting default VPC...${NC}"
VPC_ID=$(aws ec2 describe-vpcs \
  --filters "Name=is-default,Values=true" \
  --query 'Vpcs[0].VpcId' \
  --output text \
  --region $REGION)

if [ "$VPC_ID" == "None" ] || [ -z "$VPC_ID" ]; then
  echo -e "${RED}✗ No default VPC found. Please create a VPC first.${NC}"
  exit 1
fi

echo -e "${GREEN}✓ Using VPC: $VPC_ID${NC}"

# Get VPC CIDR for security group
VPC_CIDR=$(aws ec2 describe-vpcs \
  --vpc-ids $VPC_ID \
  --query 'Vpcs[0].CidrBlock' \
  --output text \
  --region $REGION)

echo -e "${GREEN}✓ VPC CIDR: $VPC_CIDR${NC}\n"

# Get subnets in the VPC
echo -e "${YELLOW}Getting subnets...${NC}"
SUBNETS=$(aws ec2 describe-subnets \
  --filters "Name=vpc-id,Values=$VPC_ID" \
  --query 'Subnets[*].SubnetId' \
  --output text \
  --region $REGION)

SUBNET_ARRAY=($SUBNETS)
SUBNET_COUNT=${#SUBNET_ARRAY[@]}

if [ $SUBNET_COUNT -lt 2 ]; then
  echo -e "${RED}✗ Need at least 2 subnets for Aurora. Found: $SUBNET_COUNT${NC}"
  exit 1
fi

echo -e "${GREEN}✓ Found $SUBNET_COUNT subnets${NC}"

# Take first 2 subnets
SUBNET1=${SUBNET_ARRAY[0]}
SUBNET2=${SUBNET_ARRAY[1]}

echo -e "${GREEN}  Using: $SUBNET1, $SUBNET2${NC}\n"

# Create DB Subnet Group
echo -e "${YELLOW}Creating DB subnet group...${NC}"
aws rds create-db-subnet-group \
  --db-subnet-group-name $SUBNET_GROUP \
  --db-subnet-group-description "Subnet group for Buypower Aurora cluster" \
  --subnet-ids $SUBNET1 $SUBNET2 \
  --region $REGION 2>&1 || echo "Subnet group may already exist"

echo -e "${GREEN}✓ DB subnet group created/verified${NC}\n"

# Create Security Group
echo -e "${YELLOW}Creating security group...${NC}"
SG_ID=$(aws ec2 create-security-group \
  --group-name $SG_NAME \
  --description "Security group for Buypower Aurora RDS" \
  --vpc-id $VPC_ID \
  --region $REGION \
  --query 'GroupId' \
  --output text 2>&1 || \
  aws ec2 describe-security-groups \
  --filters "Name=group-name,Values=$SG_NAME" "Name=vpc-id,Values=$VPC_ID" \
  --query 'SecurityGroups[0].GroupId' \
  --output text \
  --region $REGION)

echo -e "${GREEN}✓ Security Group ID: $SG_ID${NC}\n"

# Add inbound rule for MySQL from VPC
echo -e "${YELLOW}Configuring security group rules...${NC}"
aws ec2 authorize-security-group-ingress \
  --group-id $SG_ID \
  --protocol tcp \
  --port 3306 \
  --cidr $VPC_CIDR \
  --region $REGION 2>&1 || echo "Rule may already exist"

echo -e "${GREEN}✓ Security group configured${NC}\n"

# Create Aurora Cluster
echo -e "${YELLOW}Creating Aurora MySQL cluster (this takes 5-10 minutes)...${NC}"
aws rds create-db-cluster \
  --db-cluster-identifier $CLUSTER_ID \
  --engine aurora-mysql \
  --engine-version 8.0.mysql_aurora.3.05.2 \
  --master-username $MASTER_USER \
  --master-user-password "$MASTER_PASSWORD" \
  --database-name $DB_NAME \
  --db-subnet-group-name $SUBNET_GROUP \
  --vpc-security-group-ids $SG_ID \
  --backup-retention-period 7 \
  --preferred-backup-window "03:00-04:00" \
  --preferred-maintenance-window "sun:04:00-sun:05:00" \
  --storage-encrypted \
  --region $REGION

echo -e "${GREEN}✓ Aurora cluster creation initiated${NC}\n"

# Create Aurora Instance
echo -e "${YELLOW}Creating Aurora instance...${NC}"
aws rds create-db-instance \
  --db-instance-identifier $INSTANCE_ID \
  --db-instance-class $INSTANCE_CLASS \
  --engine aurora-mysql \
  --db-cluster-identifier $CLUSTER_ID \
  --publicly-accessible false \
  --region $REGION

echo -e "${GREEN}✓ Aurora instance creation initiated${NC}\n"

# Wait for cluster to be available
echo -e "${YELLOW}Waiting for Aurora cluster to become available (5-10 minutes)...${NC}"
echo -e "${YELLOW}This may take a while. Grab a coffee! ☕${NC}\n"

aws rds wait db-cluster-available \
  --db-cluster-identifier $CLUSTER_ID \
  --region $REGION

echo -e "${GREEN}✓ Aurora cluster is now available!${NC}\n"

# Get connection details
echo -e "${YELLOW}Fetching connection details...${NC}"
CLUSTER_ENDPOINT=$(aws rds describe-db-clusters \
  --db-cluster-identifier $CLUSTER_ID \
  --region $REGION \
  --query 'DBClusters[0].Endpoint' \
  --output text)

READER_ENDPOINT=$(aws rds describe-db-clusters \
  --db-cluster-identifier $CLUSTER_ID \
  --region $REGION \
  --query 'DBClusters[0].ReaderEndpoint' \
  --output text)

PORT=$(aws rds describe-db-clusters \
  --db-cluster-identifier $CLUSTER_ID \
  --region $REGION \
  --query 'DBClusters[0].Port' \
  --output text)

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Aurora RDS Setup Complete!${NC}"
echo -e "${GREEN}========================================${NC}\n"

echo -e "${GREEN}Connection Details:${NC}"
echo -e "  Writer Endpoint: ${YELLOW}$CLUSTER_ENDPOINT${NC}"
echo -e "  Reader Endpoint: ${YELLOW}$READER_ENDPOINT${NC}"
echo -e "  Port:            ${YELLOW}$PORT${NC}"
echo -e "  Database:        ${YELLOW}$DB_NAME${NC}"
echo -e "  Username:        ${YELLOW}$MASTER_USER${NC}"
echo -e "  Password:        ${YELLOW}[Your provided password]${NC}\n"

echo -e "${GREEN}Next Steps:${NC}\n"

echo -e "1. Configure Elastic Beanstalk environment:"
echo -e "${YELLOW}"
cat << EOF
eb setenv \\
  DB_CONNECTION=mysql \\
  DB_HOST="$CLUSTER_ENDPOINT" \\
  DB_PORT=$PORT \\
  DB_DATABASE=$DB_NAME \\
  DB_USERNAME=$MASTER_USER \\
  DB_PASSWORD="YOUR_PASSWORD_HERE"
EOF
echo -e "${NC}\n"

echo -e "2. Deploy your application:"
echo -e "${YELLOW}eb deploy${NC}\n"

echo -e "3. Run database migrations (SSH into EB instance):"
echo -e "${YELLOW}eb ssh${NC}"
echo -e "${YELLOW}cd /var/app/current && php artisan migrate --force${NC}\n"

echo -e "${GREEN}Setup complete!${NC}\n"


