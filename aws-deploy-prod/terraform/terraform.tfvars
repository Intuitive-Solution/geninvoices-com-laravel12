# AWS Configuration
aws_region = "ap-south-1"

# Project Configuration
project_name = "invoiceninja"
environment  = "production"

# EC2 Configuration
instance_type    = "t2.small"
ebs_volume_size  = 30
public_key_path  = "~/.ssh/id_rsa.pub"
ssh_cidr         = "0.0.0.0/0"  # Change this to your IP for better security

# Database Configuration
db_password = "apple1234"

# Application Configuration
app_key     = "base64:yEJ8ivcVr6Hc6rW8zoaUm9AxwPWKE4OT8JAGPPhSM68="
app_url     = "https://uat.geninvoices.com"
domain_name = "uat.geninvoices.com" 