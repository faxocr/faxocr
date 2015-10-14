#
# Cookbook Name:: faxocr
# Recipe:: default
#

include_recipe "faxocr::package"
include_recipe "faxocr::parallel"
include_recipe "faxocr::fonts"
include_recipe "faxocr::wkhtmltopdf"
include_recipe "faxocr::opencv"
include_recipe "faxocr::user-faxocr"
include_recipe "faxocr::faxocr"
include_recipe "faxocr::cluscore"
include_recipe "faxocr::kocr"
include_recipe "faxocr::sheetreader"
include_recipe "faxocr::srhelper"
include_recipe "faxocr::procmail"
include_recipe "faxocr::fetchmail"
include_recipe "faxocr::mysql"
include_recipe "faxocr::crontab"
include_recipe "faxocr::writable-dir-by-apache"
include_recipe "faxocr::rails"
include_recipe "faxocr::apache-conf"
