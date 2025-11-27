#!/bin/bash
set -e

REGION="us-east-1"

# Get VPC and subnets
VPC_ID=$(aws ec2 describe-vpcs --filters "Name=is-default,Values=true" --query 'Vpcs[0].VpcId' --output text --region $REGION)
VPC_CIDR=$(aws ec2 describe-vpcs --vpc-ids $VPC_ID --query 'Vpcs[0].CidrBlock' --output text --region $REGION)
SUBNETS=$(aws ec2 describe-subnets --filters "Name=vpc-id,Values=$VPC_ID" --query 'Subnets[0:2].SubnetId' --output text --region $REGION)
SUBNET_ARRAY=($SUBNETS)

echo "VPC: $VPC_ID ($VPC_CIDR)"
echo "Subnets: ${SUBNET_ARRAY[0]}, ${SUBNET_ARRAY[1]}"

# Create DB subnet group
aws rds create-db-subnet-group \
  --db-subnet-group-name buypower-aurora-subnet \
  --db-subnet-group-description "Buypower Aurora subnet group" \
  --subnet-ids ${SUBNET_ARRAY[0]} ${SUBNET_ARRAY[1]} \
  --region $REGION 2>&1 || echo "✓ Subnet group exists"

# Create security group
SG_ID=$(aws ec2 create-security-group \
  --group-name buypower-aurora-sg \
  --description "Buypower Aurora security group" \
  --vpc-id $VPC_ID \
  --region $REGION \
  --query 'GroupId' \
  --output text 2>&1 || \
  aws ec2 describe-security-groups \
  --filters "Name=group-name,Values=buypower-aurora-sg" \
  --query 'SecurityGroups[0].GroupId' \
  --output text \
  --region $REGION)

echo "✓ Security Group: $SG_ID"

# Add VPC access rule
aws ec2 authorize-security-group-ingress \
  --group-id $SG_ID \
  --protocol tcp \
  --port 3306 \
  --cidr $VPC_CIDR \
  --region $REGION 2>&1 || echo "✓ Security rule exists"

# Generate strong password
DB_PASSWORD="BuyPower$(date +%s)!Prod"

echo ""
echo "Creating Aurora cluster with password: $DB_PASSWORD"
echo "⚠️  SAVE THIS PASSWORD!"
echo ""

# Create Aurora cluster
aws rds create-db-cluster \
  --db-cluster-identifier buypower-aurora-cluster \
  --engine aurora-mysql \
  --engine-version 8.0.mysql_aurora.3.05.2 \
  --master-username admin \
  --master-user-password "$DB_PASSWORD" \
  --database-name buypower_db \
  --db-subnet-group-name buypower-aurora-subnet \
  --vpc-security-group-ids $SG_ID \
  --backup-retention-period 7 \
  --storage-encrypted \
  --region $REGION && echo "✓ Cluster created" || echo "✓ Cluster exists"

# Create instance
aws rds create-db-instance \
  --db-instance-identifier buypower-aurora-instance-1 \
  --db-instance-class db.t3.small \
  --engine aurora-mysql \
  --db-cluster-identifier buypower-aurora-cluster \
  --publicly-accessible false \
  --region $REGION && echo "✓ Instance created" || echo "✓ Instance exists"

echo ""
echo "⏳ Waiting for Aurora to be available (5-10 minutes)..."
aws rds wait db-cluster-available --db-cluster-identifier buypower-aurora-cluster --region $REGION

# Get endpoint
ENDPOINT=$(aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster \
  --region $REGION \
  --query 'DBClusters[0].Endpoint' \
  --output text)

echo ""
echo "========================================="
echo "✓ Aurora RDS Created!"
echo "========================================="
echo "Endpoint: $ENDPOINT"
echo "Database: buypower_db"
echo "Username: admin"
echo "Password: $DB_PASSWORD"
echo ""
echo "Now configuring Elastic Beanstalk..."
echo ""

# Configure EB
eb setenv \
  DB_CONNECTION=mysql \
  DB_HOST="$ENDPOINT" \
  DB_PORT=3306 \
  DB_DATABASE=buypower_db \
  DB_USERNAME=admin \
  DB_PASSWORD="$DB_PASSWORD" \
  BUYPOWER_API_KEY="test-key-replace-later"

echo "✓ EB environment configured"
echo ""
echo "Password saved to: aurora-credentials.txt"
echo "$DB_PASSWORD" > aurora-credentials.txt

echo ""
echo "Next: Deploy with 'eb deploy'"
