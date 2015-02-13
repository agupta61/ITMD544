#!/bin/bash
# The shell script has a usage pattern provide the data before hand and then reference it.
#example ./Gupta_Ambuj.sh Ambuj-LB ambujkey webserverMA3 4 ambuj-gupta-1 ambujrole
# In this shell script then value $1  would be the first argument about...Ambuj-LB
# $2 would reference the second value sandeepkey and so forth...
rm setup-ma4.sh worker-setup-ma4.sh
wget http://54.86.57.215/setup-ma4.sh
wget http://54.86.57.215/worker-setup-ma4.sh

chmod 755 setup-ma4.sh worker-setup-ma4.sh

if [ $# != 6 ]
  then
  echo "This script needs 6 arguments/variables to run; ELB-NAME, KEYPAIR, CLIENT-TOKENS, NUMBER OF INSTANCES, SECURITY-GROUP-NAME and IAM-ROLE"
else

#Mysql DATABASE creation
aws rds create-db-instance --db-instance-identifier FACLOUDDB --allocated-storage 5 --db-instance-class db.t2.micro --port 3306 --multi-az --engine MySQL --engine-version 5.6.19a --master-username userambuj --master-user-password usergupta

# SQS creation
aws sqs create-queue --queue-name FA5QUEUE --output=text

arn=`aws sns create-topic --name ImageDone|awk {' print $1'}`

aws sns subscribe --topic-arn $arn --protocol email --notification-endpoint agutpa61@hawk.iit.edu

#Step 1: VPC creation with a /28 cidr block
 vpc=`aws ec2 create-vpc --cidr-block 10.0.0.0/28|awk {' print $6'}`
 echo "vpc-id: $vpc "
 echo "  "

#Step 2: Subnet creation for the VPC with the same /28 cidr block
subnet1=`aws ec2 create-subnet --vpc-id $vpc --cidr-block 10.0.0.0/28|awk {' print $6'}`
 echo "subnet-id: $subnet1"
 echo "  "

#Step 3: Custom Security Group creation per the above VPC
SGID=`aws ec2 create-security-group --group-name $5 --description "My security group" --vpc-id $vpc --output=text|awk {' print $1'}`
 echo "security-group-id: $SGID"
 echo " "

#step 3b:  Opening the ports For SSH and WEB access to the created security group
aws ec2 authorize-security-group-ingress --group-id $SGID --protocol tcp --port 80 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SGID --protocol tcp --port 22 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SGID --protocol tcp --port 3306 --cidr 0.0.0.0/0 
 echo "PORTS opened for security-group-id: $SGID"
 echo " "

#Step 4: Internet gateway creation, so that VPC has internet access
secgrp1=`aws ec2 create-internet-gateway --output=text|awk {' print $2'}`
 echo "Internet-gateway-id: $secgrp1"
 echo " "

#step 4b:  Modifying the VPC attributes to enable dns support and enable dns hostnames
aws ec2 modify-vpc-attribute --vpc-id $vpc --enable-dns-support "{\"Value\":true}"
aws ec2 modify-vpc-attribute --vpc-id $vpc --enable-dns-hostnames "{\"Value\":true}"
 echo "DNS support enabled"
 echo "DNS hosnames enabled"
 echo " "

#Step 5 Modifying subnet attribute - telling the subnet id to --map-public-ip-on-launch
aws ec2 modify-subnet-attribute --subnet-id $subnet1 --map-public-ip-on-launch "{\"Value\":true}"
 echo "Public IP on-launch"
 echo " "

#Step 6:  Attaching the internet gateway to our VPC
aws ec2 attach-internet-gateway --internet-gateway-id $secgrp1 --vpc-id $vpc
 echo " VPC: $vpc attached to Internet-gateway: $secgrp1"
 echo " "

#Step 6b: ROUTETABLE creation
rtable1=`aws ec2 create-route-table --vpc-id $vpc --output=text| grep rtb|awk {' print $2'}`
 echo "Routetable: $rtable1"
 echo " "

#Step 6c: Creating a route to be attached to the route table
aws ec2 create-route --route-table-id $rtable1 --destination-cidr-block 0.0.0.0/0 --gateway-id $secgrp1
 echo "Route attached to Routetable: $rtable1"
 echo " "

#Step 6d: Now associating that route with a route-table-id and a subnet-id
aws ec2 associate-route-table --route-table-id $rtable1 --subnet-id $subnet1

#Step 7: Creating a ELBURL variable and a load balancer
ELBURL=`aws elb create-load-balancer --load-balancer-name $1 --listeners Protocol=HTTP,LoadBalancerPort=80,InstanceProtocol=HTTP,InstancePort=80  --subnets $subnet1 --security-groups $SGID --output=text|awk {'print $1'}`

echo -e "\nLaunching ELB...please wait for a moment..."
for i in {0..25}; do echo -ne '.'; sleep 1;done

#step 7b: ELB configure-health-check
aws elb configure-health-check --load-balancer-name $1 --health-check Target=HTTP:80/index.html,Interval=30,UnhealthyThreshold=2,HealthyThreshold=2,Timeout=3

echo -e "\nFinished ELB health check...please wait for a moment... "
for i in {0..25}; do echo -ne '.'; sleep 1;done
#azone1=`aws ec2 describe-subnets --subnet-id $subnet1 --output=text |awk {'print $2'}`

#Step 8: Launching instances
aws ec2 run-instances --image-id ami-e84d8480 --count $4 --iam-instance-profile Name=$6 --instance-type t1.micro --key-name $2 --associate-public-ip-address --security-group-ids $SGID --client-token $3 --subnet-id $subnet1  --block-device-mappings "[{\"DeviceName\" : \"/dev/xvdb\",\"Ebs\":{\"VolumeSize\" : 10}}]" --user-data file://setup-ma4.sh --output=text

echo -e "\nFinished launching EC2 Instances...please wait for a moment... "
for i in {0..60}; do echo -ne '.'; sleep 1;done

#Step 9: Declaring an array in BASH and list our instances to filter the instance-ids
declare -a ARRAY
ARRAY=`aws ec2 describe-instances --output=text | grep $3| awk {'print $8'}`
for i in {0..15}; do echo -ne '.'; sleep 1;done

#Step 10: Adding each instance to loadbalancer and print out the progress
LENGTH=${#ARRAY[@]}
echo "ARRAY LENGTH IS $LENGTH"
for (( x=0; x<${LENGTH}; x++))
  do
  echo "Registering ${ARRAY[$x]} with load-balancer $1...please wait for a moment... "
  aws elb register-instances-with-load-balancer --load-balancer-name $1 --instance ${ARRAY[$x]} --output=table
    for y in {0..60}
    do
      echo -ne '.'
      sleep 1
    done
 echo "\n"
done
#Step 8: Launching Worker Instance
aws ec2 run-instances --image-id ami-e84d8480 --count 1 --iam-instance-profile Name=$6 --instance-type t1.micro --key-name $2 --associate-public-ip-address --security-group-ids $SGID --subnet-id $subnet1  --block-device-mappings "[{\"DeviceName\" : \"/dev/xvdb\",\"Ebs\":{\"VolumeSize\" : 10}}]" --user-data file://worker-setup-ma4.sh --output=text

echo -e "\nWaiting an additional 13 minutes for ELB and Main Database Creation - before opening the ELB in a webbrowser"
for i in {0..780}; do echo -ne '.'; sleep 1;done
#Read Replica creation
aws rds create-db-instance-read-replica --db-instance-identifier ReadFACLOUDDB --source-db-instance-identifier FACLOUDDB --db-instance-class db.t2.micro --port 3306 
#rm filepush.sh

#Last Step
firefox $ELBURL &

fi
#End of if statement

