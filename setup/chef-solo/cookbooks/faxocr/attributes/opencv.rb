
case node[:platform]
when "debian"
  if node[:platform_version].to_f >= 7.0
    default[:opencv][:packages] = %w{ libopencv-dev }
  else
    default[:opencv][:packages] = %w{ libcv-dev libcv2.1 libhighgui-dev libhighgui2.1 libcvaux-dev libcvaux2.1 }
  end
when "ubuntu"
  if Gem::Version.new(node[:platform_version]) >= Gem::Version.new("14.04")
    default[:opencv][:packages] = %w{ libopencv-dev }
  elsif Gem::Version.new(node[:platform_version]) >= Gem::Version.new("12.04")
    default[:opencv][:packages] = %w{ libopencv-dev libcv-dev libcv2.3 libhighgui-dev libhighgui2.3 libcvaux-dev libcvaux2.3 }
  else
    default[:opencv][:packages] = %w{ libcv-dev libcv2.1 libhighgui-dev libhighgui2.1 libcvaux-dev libcvaux2.1 }
  end
else
  default[:opencv][:packages] = %w{ }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
