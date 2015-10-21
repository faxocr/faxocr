
default[:faxocr][:libtool][:packages] =
  case node[:platform]
  when "debian"
    if node[:platform_version].to_f >= 8.0
      "libtool-bin"
    else
      "libtool"
    end
  else
      "libtool"
  end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
