#!/bin/sh

apt-get update
apt-get upgrade -y

apt-get install -y git wget

apt-get install -y rubygems1.8 ruby1.8-dev
gem1.8 install bundler --no-ri --no-rdoc

wget -q http://faxocr.googlecode.com/git-history/master/setup/chef-solo/Gemfile
wget -q http://faxocr.googlecode.com/git-history/master/setup/chef-solo/Gemfile.lock
bundle install

git clone https://code.google.com/p/faxocr
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
