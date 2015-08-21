# encoding: UTF-8
# This file is auto-generated from the current state of the database. Instead
# of editing this file, please use the migrations feature of Active Record to
# incrementally modify your database, and then regenerate this schema definition.
#
# Note that this schema.rb definition is the authoritative source for your
# database schema. If you need to create the application database on another
# system, you should be using db:schema:load, not running all the migrations
# from scratch. The latter is a flawed and unsustainable approach (the more migrations
# you'll amass, the slower it'll run and the greater likelihood for issues).
#
# It's strongly recommended that you check this file into your version control system.

ActiveRecord::Schema.define(version: 20150710043900) do

  create_table "answer_sheet_properties", force: true do |t|
    t.integer  "answer_sheet_id", null: false
    t.string   "ocr_name",        null: false
    t.string   "ocr_value"
    t.string   "ocr_image"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "answer_sheet_properties", ["answer_sheet_id", "ocr_name"], name: "index_answer_sheet_properties_on_answer_sheet_id_and_ocr_name", unique: true, using: :btree
  add_index "answer_sheet_properties", ["answer_sheet_id"], name: "index_answer_sheet_properties_on_answer_sheet_id", using: :btree

  create_table "answer_sheets", force: true do |t|
    t.datetime "date"
    t.string   "sender_number"
    t.string   "receiver_number"
    t.integer  "sheet_id"
    t.integer  "candidate_id"
    t.string   "analyzed_sheet_code"
    t.string   "analyzed_candidate_code"
    t.string   "sheet_image"
    t.boolean  "need_check"
    t.integer  "srerror"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "answer_sheets", ["candidate_id"], name: "index_answer_sheets_on_candidate_id", using: :btree
  add_index "answer_sheets", ["sheet_id"], name: "index_answer_sheets_on_sheet_id", using: :btree

  create_table "candidates", force: true do |t|
    t.string   "candidate_code", null: false
    t.string   "candidate_name", null: false
    t.integer  "group_id",       null: false
    t.string   "tel_number"
    t.string   "fax_number"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "candidates", ["candidate_code"], name: "index_candidates_on_candidate_code", unique: true, using: :btree
  add_index "candidates", ["group_id"], name: "index_candidates_on_group_id", using: :btree

  create_table "groups", force: true do |t|
    t.string   "group_name"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "role_mappings", force: true do |t|
    t.integer  "group_id",   null: false
    t.integer  "user_id",    null: false
    t.string   "role"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "role_mappings", ["group_id", "user_id"], name: "index_role_mappings_on_group_id_and_user_id", unique: true, using: :btree

  create_table "sessions", force: true do |t|
    t.string   "session_id", null: false
    t.text     "data"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "sessions", ["session_id"], name: "index_sessions_on_session_id", using: :btree
  add_index "sessions", ["updated_at"], name: "index_sessions_on_updated_at", using: :btree

  create_table "sheet_cellattribute_colwidths", force: true do |t|
    t.integer  "sheet_cellattribute_id",            null: false
    t.integer  "col_number",                        null: false
    t.float    "size",                   limit: 24, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "sheet_cellattribute_rowcolspans", force: true do |t|
    t.integer  "sheet_cellattribute_id", null: false
    t.integer  "row_number",             null: false
    t.integer  "col_number",             null: false
    t.integer  "row_span",               null: false
    t.integer  "col_span",               null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "sheet_cellattribute_rowheights", force: true do |t|
    t.integer  "sheet_cellattribute_id",            null: false
    t.integer  "row_number",                        null: false
    t.float    "size",                   limit: 24, null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "sheet_cellattributes", force: true do |t|
    t.integer  "sheet_id",   null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "sheet_properties", force: true do |t|
    t.integer  "position_x"
    t.integer  "position_y"
    t.integer  "colspan"
    t.integer  "sheet_id"
    t.integer  "survey_property_id"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  create_table "sheets", force: true do |t|
    t.string   "sheet_code",   null: false
    t.string   "sheet_name",   null: false
    t.integer  "survey_id",    null: false
    t.integer  "block_width",  null: false
    t.integer  "block_height", null: false
    t.integer  "status",       null: false
    t.datetime "created_at"
    t.datetime "updated_at"
    t.text     "cell_width"
    t.text     "cell_height"
    t.text     "cell_colspan"
    t.text     "cell_rowspan"
  end

  add_index "sheets", ["sheet_code"], name: "index_sheets_on_sheet_code", unique: true, using: :btree
  add_index "sheets", ["survey_id"], name: "index_sheets_on_survey_id", using: :btree

  create_table "survey_candidates", force: true do |t|
    t.integer  "survey_id",    null: false
    t.integer  "candidate_id", null: false
    t.string   "role"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "survey_candidates", ["survey_id", "candidate_id"], name: "index_survey_candidates_on_survey_id_and_candidate_id", unique: true, using: :btree

  create_table "survey_properties", force: true do |t|
    t.integer  "survey_id",     null: false
    t.string   "ocr_name",      null: false
    t.string   "ocr_name_full", null: false
    t.integer  "view_order",    null: false
    t.string   "data_type",     null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "survey_properties", ["survey_id", "ocr_name"], name: "index_survey_properties_on_survey_id_and_ocr_name", unique: true, using: :btree

  create_table "survey_users", force: true do |t|
    t.integer  "survey_id",  null: false
    t.integer  "user_id",    null: false
    t.boolean  "owner",      null: false
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "survey_users", ["survey_id", "user_id"], name: "index_survey_users_on_survey_id_and_user_id", unique: true, using: :btree

  create_table "surveys", force: true do |t|
    t.string   "survey_name",   null: false
    t.integer  "group_id",      null: false
    t.integer  "status",        null: false
    t.integer  "sheet_id"
    t.text     "report_header"
    t.text     "report_footer"
    t.datetime "created_at"
    t.datetime "updated_at"
    t.time     "report_time"
    t.string   "report_wday"
  end

  add_index "surveys", ["group_id"], name: "index_surveys_on_group_id", using: :btree

  create_table "users", force: true do |t|
    t.string   "login_name"
    t.string   "full_name"
    t.string   "hashed_password"
    t.string   "salt"
    t.string   "organization"
    t.string   "section"
    t.string   "tel_number"
    t.string   "fax_number"
    t.string   "email"
    t.datetime "created_at"
    t.datetime "updated_at"
  end

  add_index "users", ["login_name"], name: "index_users_on_login_name", unique: true, using: :btree

end
