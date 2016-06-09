#!/bin/sh

apt-get update
apt-get upgrade -y

apt-get install -y git wget make

# Debian 8
if [ -f /etc/debian_version ] && `grep '^8' /etc/debian_version >/dev/null 2>&1`; then
        apt-get install -y ruby ruby-dev build-essential libffi-dev
else
        apt-get install -y ruby1.9.1-dev
fi

gem install bundler --no-ri --no-rdoc

wget -q https://raw.githubusercontent.com/faxocr/faxocr/master/setup/chef-solo/Gemfile
wget -q https://raw.githubusercontent.com/faxocr/faxocr/master/setup/chef-solo/Gemfile.lock
bundle install

git clone https://github.com/faxocr/faxocr
sed -i'' -e "s#%%EDIT_ME%%#`pwd`#" faxocr/setup/chef-solo/solo.rb
(cd faxocr;
git submodule init;
git submodule update;
)

(cd faxocr/setup/chef-solo;
chef-solo -c `pwd`/solo.rb -j `pwd`/nodes/localhost.json;
)

(cd faxocr/setup/serverspec;
rake spec;
)
