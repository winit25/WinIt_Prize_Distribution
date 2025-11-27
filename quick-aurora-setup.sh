#!/bin/bash

# Quick Aurora Setup - Copy these commands one by one
# Replace YOUR_PASSWORD with your actual strong password

# 1. Check if Aurora cluster exists
aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster \
  --region us-east-1 2>&1 | grep "DBClusterIdentifier" && echo "Cluster exists!" || echo "Cluster not found, proceeding with creation..."

# 2. Get default VPC ID
VPC_ID=$(aws ec2 describe-vpcs \
  --filters "Name=is-default,Values=true" \
  --query 'Vpcs[0].VpcId' \
  --output text \
  --region us-east-1)

echo "Using VPC: $VPC_ID"

# 3. Get VPC CIDR
VPC_CIDR=$(aws ec2 describe-vpcs \
  --vpc-ids $VPC_ID \
  --query 'Vpcs[0].CidrBlock' \
  --output text \
  --region us-east-1)

echo "VPC CIDR: $VPC_CIDR"

# 4. Get subnets
SUBNETS=$(aws ec2 describe-subnets \
  --filters "Name=vpc-id,Values=$VPC_ID" \
  --query 'Subnets[0:2].SubnetId' \
  --output text \
  --region us-east-1)

SUBNET_ARRAY=($SUBNETS)
echo "Using subnets: ${SUBNET_ARRAY[0]} ${SUBNET_ARRAY[1]}"

# 5. Create DB subnet group
aws rds create-db-subnet-group \
  --db-subnet-group-name buypower-aurora-subnet \
  --db-subnet-group-description "Subnet group for Buypower Aurora" \
  --subnet-ids ${SUBNET_ARRAY[0]} ${SUBNET_ARRAY[1]} \
  --region us-east-1 2>&1 || echo "Subnet group already exists"

# 6. Create security group
SG_ID=$(aws ec2 create-security-group \
  --group-name buypower-aurora-sg \
  --description "Security group for Buypower Aurora" \
  --vpc-id $VPC_ID \
  --region us-east-1 \
  --query 'GroupId' \
  --output text 2>&1 || \
  aws ec2 describe-security-groups \
  --filters "Name=group-name,Values=buypower-aurora-sg" \
  --query 'SecurityGroups[0].GroupId' \
  --output text \
  --region us-east-1)

echo "Security Group: $SG_ID"

# 7. Add MySQL access from VPC
aws ec2 authorize-security-group-ingress \
  --group-id $SG_ID \
  --protocol tcp \
  --port 3306 \
  --cidr $VPC_CIDR \
  --region us-east-1 2>&1 || echo "Rule already exists"

# ⚠️ IMPORTANT: Set your password here
DB_PASSWORD="YourStrongPassword123!"  # CHANGE THIS!

# 8. Create Aurora cluster (5-10 minutes)
echo "Creating Aurora cluster..."
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
  --region us-east-1

# 9. Create Aurora instance
echo "Creating Aurora instance..."
aws rds create-db-instance \
  --db-instance-identifier buypower-aurora-instance-1 \
  --db-instance-class db.t3.small \
  --engine aurora-mysql \
  --db-cluster-identifier buypower-aurora-cluster \
  --publicly-accessible false \
  --region us-east-1

# 10. Wait for cluster
echo "Waiting for Aurora (5-10 minutes)..."
aws rds wait db-cluster-available \
  --db-cluster-identifier buypower-aurora-cluster \
  --region us-east-1

# 11. Get endpoint
ENDPOINT=$(aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster \
  --region us-east-1 \
  --query 'DBClusters[0].Endpoint' \
  --output text)

echo ""
echo "========================================="
echo "Aurora RDS Created Successfully!"
echo "========================================="
echo "Endpoint: $ENDPOINT"
echo "Database: buypower_db"
echo "Username: admin"
echo "Password: $DB_PASSWORD"
echo ""
echo "Next: Configure EB with these credentials"
echo ""
echo "Run this command:"
echo "eb setenv DB_CONNECTION=mysql DB_HOST=\"$ENDPOINT\" DB_PORT=3306 DB_DATABASE=buypower_db DB_USERNAME=admin DB_PASSWORD=\"$DB_PASSWORD\""

