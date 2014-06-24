
default[:faxocr][:wkhtmltopdf][:install_path] = "/usr/local/bin"

case node[:languages][:ruby][:host_cpu]
when "x86_64"
  default[:faxocr][:wkhtmltopdf][:archive] = "wkhtmltox-linux-amd64_0.12.0-03c001d.tar.xz"
when "i686"
  default[:faxocr][:wkhtmltopdf][:archive] = "wkhtmltox-linux-i386_0.12.0-03c001d.tar.xz"
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
