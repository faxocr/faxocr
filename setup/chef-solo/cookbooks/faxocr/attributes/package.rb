default[:faxocr][:package][:osdependent] =
  case node[:platform]
  when "debian"
    if node[:platform_version].to_f >= 8.0
      %w{ fonts-vlgothic }
    else
      %w{ ttf-vlgothic }
    end
  when "ubuntu"
    if Gem::Version.new(node[:platform_version]) >= Gem::Version.new("14.04")
      %w{ fonts-vlgothic }
    else
      %w{ ttf-vlgothic }
    end
  else
    %w{ }
  end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
