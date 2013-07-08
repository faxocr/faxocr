
default[:faxocr][:wkhtmltopdf][:install_path] = "/usr/local/bin"

case node[:languages][:ruby][:host_cpu]
when "x86_64"
  default[:faxocr][:wkhtmltopdf][:archive] = "wkhtmltopdf-0.11.0_rc1-static-amd64.tar.bz2"
  default[:faxocr][:wkhtmltopdf][:binary_filename] = "wkhtmltopdf-amd64"
when "i686"
  default[:faxocr][:wkhtmltopdf][:archive] = "wkhtmltopdf-0.11.0_rc1-static-i386.tar.bz2"
  default[:faxocr][:wkhtmltopdf][:binary_filename] = "wkhtmltopdf-i386"
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
