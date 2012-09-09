require 'test_helper'

class MasqueradersControllerTest < ActionController::TestCase
  test "should get index" do
    get :index
    assert_response :success
    assert_not_nil assigns(:masqueraders)
  end

  test "should get new" do
    get :new
    assert_response :success
  end

  test "should create masquerader" do
    assert_difference('Masquerader.count') do
      post :create, :masquerader => { }
    end

    assert_redirected_to masquerader_path(assigns(:masquerader))
  end

  test "should show masquerader" do
    get :show, :id => masqueraders(:one).to_param
    assert_response :success
  end

  test "should get edit" do
    get :edit, :id => masqueraders(:one).to_param
    assert_response :success
  end

  test "should update masquerader" do
    put :update, :id => masqueraders(:one).to_param, :masquerader => { }
    assert_redirected_to masquerader_path(assigns(:masquerader))
  end

  test "should destroy masquerader" do
    assert_difference('Masquerader.count', -1) do
      delete :destroy, :id => masqueraders(:one).to_param
    end

    assert_redirected_to masqueraders_path
  end
end
