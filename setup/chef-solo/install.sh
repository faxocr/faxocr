#!/bin/sh

aptitude update
aptitude upgrade -y

aptitude install -y git

aptitude install -y rubygems1.8 ruby1.8-dev
gem1.8 install chef --no-ri --no-rdoc
gem1.8 install knife-solo --no-ri --no-rdoc

git clone https://code.google.com/p/faxocr
sed -i'' -e "s#%%EDIT_ME%%#`pwd`#" faxocr/setup/chef-solo/solo.rb
(cd faxocr; \
git submodule init; \
git submodule update; \
)
(cd faxocr/setup/chef-solo; \
chef-solo -c `pwd`/solo.rb -j `pwd`/nodes/localhost.json; \
)
