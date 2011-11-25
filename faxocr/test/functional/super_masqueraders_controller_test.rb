require 'test_helper'

class SuperMasqueradersControllerTest < ActionController::TestCase
  test "should get index" do
    get :index
    assert_response :success
    assert_not_nil assigns(:super_masqueraders)
  end

  test "should get new" do
    get :new
    assert_response :success
  end

  test "should create super_masquerader" do
    assert_difference('SuperMasquerader.count') do
      post :create, :super_masquerader => { }
    end

    assert_redirected_to super_masquerader_path(assigns(:super_masquerader))
  end

  test "should show super_masquerader" do
    get :show, :id => super_masqueraders(:one).to_param
    assert_response :success
  end

  test "should get edit" do
    get :edit, :id => super_masqueraders(:one).to_param
    assert_response :success
  end

  test "should update super_masquerader" do
    put :update, :id => super_masqueraders(:one).to_param, :super_masquerader => { }
    assert_redirected_to super_masquerader_path(assigns(:super_masquerader))
  end

  test "should destroy super_masquerader" do
    assert_difference('SuperMasquerader.count', -1) do
      delete :destroy, :id => super_masqueraders(:one).to_param
    end

    assert_redirected_to super_masqueraders_path
  end
end
