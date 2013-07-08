
node[:opencv][:packages].each do |pkg|
	package pkg do
  	action :install
	end
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
