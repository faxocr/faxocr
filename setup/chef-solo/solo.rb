PREFIX="%%EDIT_ME%%/faxocr/setup/chef-solo"
file_cache_path           "#{PREFIX}"
data_bag_path             "#{PREFIX}/data_bags"
encrypted_data_bag_secret "#{PREFIX}/data_bag_key"
cookbook_path             [ "#{PREFIX}/site-cookbooks",
                            "#{PREFIX}/cookbooks" ]
role_path                 "#{PREFIX}/roles"
