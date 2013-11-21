ActionController::Routing::Routes.draw do |map|

  map.resources :configs, :collection => { :index => :get, :view_system_config => :get, :update => :post, :database => :get, :view_procfax_log => :get, :procfax_exec => :post, :getfax_exec => :post, :cron => :get, :note => :get, :note_update => :post, :view_sendfax_log => :get, :sendtestfax_exec => :post, :view_faxmail_queue => :get, :view_answer_sheet => :get }

  map.resources :role_mappings

  map.resources :groups, :member => { :report => :get } do |group|
    group.resources :surveys do |survey|
      survey.resources :sheets do |sheet|
        sheet.resources :sheet_properties
      end
      survey.resources :survey_properties
      survey.resources :survey_candidates
      survey.resources :answer_sheets, :member => { :image => :get, :image_thumb => :get, :edit_recognize => :get, :update_recognize => :put } do |answer_sheet|
        answer_sheet.resources :answer_sheet_properties, :member => { :image => :get }
      end
      survey.connect "report/:year/:month/:day",
        :controller => "report",
        :action => "daily",
        :requirements => {:year => /(19|20)\d\d/,
                          :month => /[01]?\d/,
                          :day => /[0-3]\d/},
        :day => nil,
        :month => nil
      survey.connect "export/:year/:month/:day",
        :controller => "export",
        :action => "csv",
        :requirements => {:year => /(19|20)\d\d/,
                          :month => /[01]?\d/,
                          :day => /[0-3]\d/},
        :day => nil,
        :month => nil
    end
    group.resources :candidates
    group.resources :users, :member => {:edit_self => :get, :update_self => :post}
  end

  map.contact '/answer_sheets', :controller => 'answer_sheets', :action => 'index_all'
  map.resources :answer_sheets, 
    :collection => { :index_all => :get }, 
    :member => { :show_all => :get, :image_thumb_all => :get }

  # The priority is based upon order of creation: first created -> highest priority.

  # Sample of regular route:
  #   map.connect 'products/:id', :controller => 'catalog', :action => 'view'
  # Keep in mind you can assign values other than :controller and :action

  #
  # PHP driver (Target registration)
  #
  map.connect 'external/register/:group_id',
    :controller => 'external',
    :action => 'register'

  map.connect 'external/reg_upload',
    :controller => 'external',
    :action => 'reg_upload'

  map.connect 'external/reg_exec',
    :controller => 'external',
    :action => 'reg_exec'

  # PHP driver (Sheet registration Debug)
  map.connect 'external/sheet_checker',
    :controller => 'external',
    :action => 'sheet_checker'

  map.connect 'external/sht_field_checker',
    :controller => 'external',
    :action => 'sht_field_checker'

  #
  # PHP driver (Sheet registration)
  #
  map.connect 'external/sheet/:group_id/:survey_id',
    :controller => 'external',
    :action => 'sheet'

  map.connect 'external/sht_field',
    :controller => 'external',
    :action => 'sht_field'

  map.connect 'external/sht_script',
    :controller => 'external',
    :action => 'sht_script'

  map.connect 'external/sht_marker',
    :controller => 'external',
    :action => 'sht_marker'

  map.connect 'external/sht_config',
    :controller => 'external',
    :action => 'sht_config'

  map.connect 'external/sht_verify',
    :controller => 'external',
    :action => 'sht_verify'

  map.connect 'external/sht_commit',
    :controller => 'external',
    :action => 'sht_commit'

  # PHP driver
  map.connect 'external/:action',
    :controller => 'external'

  map.connect 'external/download/:group_id/:survey_id',
    :controller => 'external',
    :action => 'download'

  map.connect 'external/download_zip/:group_id/:survey_id',
    :controller => 'external',
    :action => 'download_zip'

  map.connect 'external/getimg/:group_id/:survey_id',
    :controller => 'external',
    :action => 'getimg'

  map.connect 'faxocr/direct_masquerade/:group_id/:id',
    :controller => 'faxocr',
    :action => 'direct_masquerade'

  map.connect 'faxocr/:action',
    :controller => 'faxocr'

  map.connect "inbox/",
    :controller => "inbox",
    :action => "index"

  map.connect "inbox/:group_id",
    :controller =>"inbox",
    :action => "group_surveys"

  map.connect "inbox/:group_id/:survey_id/",
    :controller => "inbox",
    :action => "survey_answer_sheets"

  map.connect "inbox/:group_id/:survey_id/:answer_sheet_id/",
    :controller => "inbox",
    :action => "answer_sheet_properties"

  map.connect "inbox/:group_id/:survey_id/:answer_sheet_id/update",
    :controller => "inbox",
    :action => "update_answer_sheet_properties"

  map.connect "report/:survey_id/daily/:year/:month/:day",
    :controller => "report",
    :action => "daily",
    :requirements => {:year => /(19|20)\d\d/,
                      :month => /[01]?\d/,
                      :day => /[0-3]\d/},
    :day => nil,
    :month => nil

  map.connect "util/survey/:survey_code/fax_numbers",
    :controller => "util",
    :action => "survey_fax_numbers"

  map.connect "util/sheet/:survey_code/srml",
    :controller => "util",
    :action => "srml"

  # Sample of named route:
  #   map.purchase 'products/:id/purchase', :controller => 'catalog', :action => 'purchase'
  # This route can be invoked with purchase_url(:id => product.id)

  # Sample resource route (maps HTTP verbs to controller actions automatically):
  #   map.resources :products

  # Sample resource route with options:
  #   map.resources :products, :member => { :short => :get, :toggle => :post }, :collection => { :sold => :get }

  # Sample resource route with sub-resources:
  #   map.resources :products, :has_many => [ :comments, :sales ], :has_one => :seller
  
  # Sample resource route with more complex sub-resources
  #   map.resources :products do |products|
  #     products.resources :comments
  #     products.resources :sales, :collection => { :recent => :get }
  #   end

  # Sample resource route within a namespace:
  #   map.namespace :admin do |admin|
  #     # Directs /admin/products/* to Admin::ProductsController (app/controllers/admin/products_controller.rb)
  #     admin.resources :products
  #   end

  # You can have the root of your site routed with map.root -- just remember to delete public/index.html.
  # map.root :controller => "welcome"

  # See how all your routes lay out with "rake routes"

  # Install the default routes as the lowest priority.
  # Note: These default routes make all actions in every controller accessible via GET requests. You should
  # consider removing or commenting them out if you're using named routes and resources.
  #map.connect ':controller/:action/:id'
  #map.connect ':controller/:action/:id.:format'
end
