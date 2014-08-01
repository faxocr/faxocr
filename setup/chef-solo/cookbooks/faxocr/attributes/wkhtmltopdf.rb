
default[:faxocr][:wkhtmltopdf][:version] = "0.12.1"
version = default[:faxocr][:wkhtmltopdf][:version]

case node[:platform]
when "debian"
  codename = "wheezy"
when "ubuntu"
  if Gem::Version.new(node[:platform_version]) >= Gem::Version.new("14.04")
    codename = "trusty"
  else
    codename = "precise"
  end
else
  codename =  node[:lsb][:codename]
end

case node[:languages][:ruby][:host_cpu]
when "x86_64"
  os_arch = "amd64"
when "i686"
  os_arch = "i386"
end
default[:faxocr][:wkhtmltopdf][:archive] = "wkhtmltox-#{version}_linux-#{codename}-#{os_arch}.deb"
default[:faxocr][:wkhtmltopdf][:url] = "http://downloads.sourceforge.net/project/wkhtmltopdf/#{version}/#{default[:faxocr][:wkhtmltopdf][:archive]}"

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
