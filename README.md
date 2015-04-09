# faxocr

## About

Shinsai Faxocr is a web application that is desinged to gather data from every fax users. It recognizes letters in fax images through another ocr driver engine, sheet-reader, also hosted on the Google code. 

## Releases

This project needs the Ruby on Rails environment. We are not providing tar balls. To set up the faxocr system, please execute the following commands: 

    git clone https://github.com/faxocr/faxocr.git faxocr
    cd faxocr
    gem update --system 1.5.3
    gem install rails -y -v 2.3.5
    cd faxocr
    vi config/database.yam
    rake db:create
    rake db:migrate
    script/server

## Releases for EC2

We are providing an AMI(Amazon Machine Image) for faxocr (though the version of the system in this image is obsolete). You can install the system in 3 minuets. You should search "faxocr" in AMIs of Tokyo Region on the AWS Management Console and lunch it. please use something like the following a user data in the Request Instance Wizard. 

    # For the pop3 server
    POP3HOST=pop3.live.com
    POP3PORT=995
    POP3USER=xxxxxxxxxxx@hotmail.com
    POP3PASSWORD=xxxxxxxxxxx
    POP3SSL=true
    # For the smtp server
    SMTPHOST=smtp.live.com
    SMTPPORT=25
    SMTPAUTH=true
    SMTPUSER=xxxxxxxxxxx@hotmail.com
    SMTPPASSWORD=xxxxxxxxxxx
    SMTPSSL=true
    SMTPFROM=xxxxxxxxxxx@hotmail.com
    # For the fax service
    FAXTODOMAIN=ml.faximo.jp
    FAXFROMADDR=xxxxxxxxxxx@hotmail.com
    FAXUSER=
    FAXPASS=

see <http://sites.google.com/site/faxocr2010/install-documents/aws>. 

