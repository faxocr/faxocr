
bash "installing srhelper" do
  cwd "#{node[:faxocr][:home_dir]}/src/srhelper"
  user "faxocr"
  group "faxocr"
  code <<-EOH
    make && make install
    EOH
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
