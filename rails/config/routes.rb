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

      match 'report/:year(/:month(/:day))' => 'report#daily', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }
      match 'report/:year(/:month(/:day))/fax_preview' => 'report#fax_preview', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }
      match 'export/:year(/:month(/:day))' => 'export#csv', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }
    end

    resources :candidates
    resources :users do
      member do
        post :update_self
        get :edit_self
      end
    end
  end

  match '/answer_sheets' => 'answer_sheets#index_all', :as => :contact
  resources :answer_sheets do
    collection do
      get :index_all
    end
    member do
      get :show_all
      get :image_thumb_all
    end
  end

  #
  # PHP driver (Target registration)
  #
  match 'external/register/:group_id' => 'external#register'
  match 'external/reg_upload' => 'external#reg_upload'
  match 'external/reg_exec' => 'external#reg_exec'
  match 'external/sheet_checker' => 'external#sheet_checker'
  match 'external/sht_field_checker' => 'external#sht_field_checker'

  #
  # PHP driver (Sheet registration)
  #
  match 'external/sheet/:group_id/:survey_id' => 'external#sheet'
  match 'external/sht_field' => 'external#sht_field'
  match 'external/sht_script' => 'external#sht_script'
  match 'external/sht_marker' => 'external#sht_marker'
  match 'external/sht_config' => 'external#sht_config'
  match 'external/sht_verify' => 'external#sht_verify'
  match 'external/sht_commit' => 'external#sht_commit'

  # PHP driver
  match 'external/:action' => 'external#index'
  match 'external/download/:group_id/:survey_id' => 'external#download'
  match 'external/download_zip/:group_id/:survey_id' => 'external#download_zip'
  match 'external/download_html/:group_id/:survey_id' => 'external#download_html'
  match 'external/getimg/:group_id/:survey_id' => 'external#getimg'

  match 'faxocr/direct_masquerade/:group_id/:id' => 'faxocr#direct_masquerade'
  match 'faxocr(/:action)', :controller => 'faxocr'

  match 'inbox/' => 'inbox#index'
  match 'inbox/:group_id' => 'inbox#group_surveys'
  match 'inbox/:group_id/:survey_id/' => 'inbox#survey_answer_sheets'
  match 'inbox/:group_id/:survey_id/:answer_sheet_id/' => 'inbox#answer_sheet_properties'
  match 'inbox/:group_id/:survey_id/:answer_sheet_id/update' => 'inbox#update_answer_sheet_properties'

  match 'report/:survey_id/daily/:year(/:month(/:day))' => 'report#daily', :constraints => { :month => /[01]?\d/, :year => /(19|20)\d\d/, :day => /[0-3]\d/ }

  match 'util/survey/:survey_code/fax_numbers' => 'util#survey_fax_numbers'
  match 'util/sheet/:sheet_code/srml' => 'util#get_one_srml_entry'
  match 'util/sheet/srml' => 'util#get_srml_contents'
end
