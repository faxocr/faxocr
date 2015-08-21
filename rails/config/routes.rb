Faxocr::Application.routes.draw do

  resources :configs do
    collection do
      post :note_update
      get  :view_procfax_log
      get  :database
      get  :view_sendfax_log
      get  :download_all_procfax_log
      post :sendtestfax_exec
      get  :note
      post :procfax_exec
      get  :view_faxmail_queue
      post :getfax_exec
      get  :view_answer_sheet
      get  :cron
      post :update_system_config
      get  :index
      get  :view_system_config
    end
  end

  resources :role_mappings

  resources :groups do
    member do
      get :report
    end
    resources :surveys do
      member do
        get :report
        put :update_report
      end
      resources :sheets do
        resources :sheet_properties
      end
      resources :survey_properties
      resources :survey_candidates
      resources :answer_sheets do
        member do
          put :update_recognize
          patch :update_answer_sheet_properties
          get :image
          get :image_thumb
          get :edit_recognize
        end
        resources :answer_sheet_properties do
          member do
            get :image
          end
        end
      end

      get 'report/:year(/:month(/:day))' => 'report#daily', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }
      get 'report/:year(/:month(/:day))/fax_preview' => 'report#fax_preview', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }
      get 'export/:year(/:month(/:day))' => 'export#csv', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }
    end

    resources :candidates
    resources :users do
      member do
        post :update_self
        get :edit_self
      end
    end
  end

  get '/answer_sheets' => 'answer_sheets#index_all', :as => :contact
  resources :answer_sheets do
    collection do
      get :index_all
    end
    member do
      get :show_all
      get :image_thumb_all
    end
  end

  namespace :external do
    #
    # PHP driver (Target registration)
    #
    get 'register/:group_id', :action => 'register'
    get 'reg_upload', :action => 'reg_upload'
    get 'reg_exec', :action => 'reg_exec'
    get 'sheet_checker', :action => 'sheet_checker' #get
    post 'sht_field_checker', :action => 'sht_field_checker'  #post

    #
    # PHP driver (Sheet registration)
    #
    get 'sheet/:group_id/:survey_id', :action => 'sheet'
    post 'sht_field', :action => 'sht_field'
    post 'sht_script', :action => 'sht_script'
    post 'sht_marker', :action => 'sht_marker'  # also get
    post 'sht_config', :action => 'sht_config'
    post 'sht_verify', :action => 'sht_verify'
    post 'sht_commit', :action => 'sht_commit'

    # PHP driver
    get ':action', :action => 'index'
    get 'download/:group_id/:survey_id', :action => 'download'  #get
    get 'download_zip/:group_id/:survey_id', :action => 'download_zip'
    get 'download_html/:group_id/:survey_id', :action => 'download_html'
    get 'getimg/:group_id/:survey_id', :action => 'getimg'  # get
  end

  namespace :faxocr do
    get 'direct_masquerade/:group_id/:id', :action => :direct_masquerade
    match :masquerade, :via => [:get, :post]
    match :login, :via => [:get, :post]
    match :group_select, :via => [:get, :post]
    get :logout
    get :index
    get '', :action => :index
  end

  namespace :inbox do
    get '', :action => 'index'
    get ':group_id', :action => 'group_surveys'
    get ':group_id/:survey_id/', :action => 'survey_answer_sheets'
    get ':group_id/:survey_id/:answer_sheet_id/', :action => 'answer_sheet_properties'
    post ':group_id/:survey_id/:answer_sheet_id/update', :action => 'update_answer_sheet_properties'
  end

  get 'report/:survey_id/daily/:year(/:month(/:day))' => 'report#daily', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }

  namespace :util do
    get 'survey/:survey_code/fax_numbers', :action => 'survey_fax_numbers'
    get 'sheet/:sheet_code/srml', :action => 'get_one_srml_entry'
    get 'sheet/srml', :action => 'get_srml_contents'
  end
end
