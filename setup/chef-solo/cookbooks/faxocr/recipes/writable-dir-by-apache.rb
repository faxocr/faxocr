
# apache is a member of faxocr group
group "faxocr" do
  action :modify
  members "www-data"
  append true
end

# group writable
# XXX: not enough?
%w{rails/files
  rails/faxocr_config/receive_sheetreader/srml
  rails/faxocr_config/recognize_sheetreader/srml
  etc
}.each do |d|
  dir = node[:faxocr][:home_dir] + '/' + d
  directory dir do
    mode 00775
    action :create
    recursive true  # XXX: not working?
  end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
