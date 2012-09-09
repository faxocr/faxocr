require 'test_helper'

class SheetPropertiesControllerTest < ActionController::TestCase
  test "should get index" do
    get :index
    assert_response :success
    assert_not_nil assigns(:sheet_properties)
  end

  test "should get new" do
    get :new
    assert_response :success
  end

  test "should create sheet_property" do
    assert_difference('SheetProperty.count') do
      post :create, :sheet_property => { }
    end

    assert_redirected_to sheet_property_path(assigns(:sheet_property))
  end

  test "should show sheet_property" do
    get :show, :id => sheet_properties(:one).to_param
    assert_response :success
  end

  test "should get edit" do
    get :edit, :id => sheet_properties(:one).to_param
    assert_response :success
  end

  test "should update sheet_property" do
    put :update, :id => sheet_properties(:one).to_param, :sheet_property => { }
    assert_redirected_to sheet_property_path(assigns(:sheet_property))
  end

  test "should destroy sheet_property" do
    assert_difference('SheetProperty.count', -1) do
      delete :destroy, :id => sheet_properties(:one).to_param
    end

    assert_redirected_to sheet_properties_path
  end
end
