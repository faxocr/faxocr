
bash "change ruby and gem to ruby1.8 base" do
  # XXX: It is dangerous to use a relative number of "1"
  code <<-EOH
    echo 1 | update-alternatives --config ruby
    echo 1 | update-alternatives --config gem
    EOH
  action :nothing
end

bash "downgrading gem to version 1.3.7" do
  cwd "#{node[:faxocr][:home_dir]}/rails"
  code <<-EOH
    gem1.8 install rubygems-update -v=1.3.7
    update_rubygems
    EOH
  only_if { node[:faxocr][:rails_base_version] == "rails2" }
end

bash "installing bundler and bundle install" do
  cwd "#{node[:faxocr][:home_dir]}/rails"
  code <<-EOH
    gem1.8 install bundler
    bundle install
    EOH
end

bash "setup bundler for ruby on rails and create DBs and tables" do
  cwd "#{node[:faxocr][:home_dir]}/rails"
  user "faxocr"
  group "faxocr"
  code <<-EOH
    bundle exec rake db:migrate RAILS_ENV=development
    bundle exec rake db:seed RAILS_ENV=development
    bundle exec rake db:migrate RAILS_ENV=production
    bundle exec rake db:seed RAILS_ENV=production
    EOH
  only_if { node[:faxocr][:setup_mode] == "initial_setup" }
end

bash "db migration of RoR" do
  cwd "#{node[:faxocr][:home_dir]}/rails"
  user "faxocr"
  group "faxocr"
  code <<-EOH
    bundle exec rake db:migrate RAILS_ENV=development
    bundle exec rake db:migrate RAILS_ENV=production
    EOH
  only_if { node[:faxocr][:setup_mode] == "production_update" }
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
