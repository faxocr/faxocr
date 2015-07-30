bash "installing bundler and bundle install" do
  cwd "#{node[:faxocr][:home_dir]}/rails"
  code <<-EOH
    gem install bundler
    bundle install --path vendor/bundler
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

bash "completely precompiling assets" do
  cwd "#{node[:faxocr][:home_dir]}/rails"
  user "faxocr"
  group "faxocr"
  code <<-EOH
    bundle exec rake assets:clean
    bundle exec rake assets:precompile
    EOH
end

# vim:set expandtab shiftwidth=2 tabstop=2 softtabstop=2:
