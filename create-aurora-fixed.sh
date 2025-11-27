#!/bin/bash
set -e

REGION="us-east-1"
DB_PASSWORD="BuyPower$(date +%s)!Prod"

echo "Password: $DB_PASSWORD"
echo "$DB_PASSWORD" > aurora-password.txt
echo ""

# Create Aurora cluster with correct version
echo "Creating Aurora cluster..."
aws rds create-db-cluster \
  --db-cluster-identifier buypower-aurora-cluster \
  --engine aurora-mysql \
  --engine-version 8.0.mysql_aurora.3.04.2 \
  --master-username admin \
  --master-user-password "$DB_PASSWORD" \
  --database-name buypower_db \
  --db-subnet-group-name buypower-aurora-subnet \
  --vpc-security-group-ids sg-0951f3a711c56312c \
  --backup-retention-period 7 \
  --storage-encrypted \
  --region $REGION

echo "✓ Cluster creation initiated"

# Create instance
echo "Creating Aurora instance..."
aws rds create-db-instance \
  --db-instance-identifier buypower-aurora-instance-1 \
  --db-instance-class db.t3.small \
  --engine aurora-mysql \
  --db-cluster-identifier buypower-aurora-cluster \
  --publicly-accessible false \
  --region $REGION

echo "✓ Instance creation initiated"
echo ""
echo "⏳ Waiting for Aurora (5-10 minutes)..."

# Wait for cluster
aws rds wait db-cluster-available --db-cluster-identifier buypower-aurora-cluster --region $REGION

# Get endpoint
ENDPOINT=$(aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster \
  --region $REGION \
  --query 'DBClusters[0].Endpoint' \
  --output text)

echo ""
echo "========================================="
echo "✓ Aurora RDS Ready!"
echo "========================================="
echo "Endpoint: $ENDPOINT"
echo "Database: buypower_db"
echo "Username: admin"
echo "Password: $DB_PASSWORD (saved to aurora-password.txt)"
echo ""

# Configure EB
echo "Configuring Elastic Beanstalk..."
eb setenv \
  DB_CONNECTION=mysql \
  DB_HOST="$ENDPOINT" \
  DB_PORT=3306 \
  DB_DATABASE=buypower_db \
  DB_USERNAME=admin \
  DB_PASSWORD="$DB_PASSWORD" \
  BUYPOWER_API_KEY="temp-key-replace-later"

echo "✓ EB configured!"
echo ""
echo "Next: eb deploy"
