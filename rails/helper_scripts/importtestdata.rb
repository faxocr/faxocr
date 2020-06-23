#!/usr/bin/ruby
# -*- coding: utf-8 -*-

require File.expand_path('../../config/boot',  __FILE__)
require "rubygems"
require "active_record"
require "yaml"

config_db = RAILS_ROOT + "/config/database.yml"
db_env = ENV['RAILS_ENV'] || ENV['RACK_ENV'] || "development"

GROUP_NAME1 = '沖縄県新型インフルエンザ小児医療情報ネットワーク'
SHEET_CODE1 = "00000"

ActiveRecord::Base.configurations = YAML.load_file(config_db)
ActiveRecord::Base.establish_connection(db_env)

Dir.glob(RAILS_ROOT + '/app/models/*.rb').each do |model|
  load model
end

user = User.new
user.login_name = 'miyata'
user.password = 'doraemon'
user.full_name = 'Miyata'
user.save

group = Group.new
group.group_name = GROUP_NAME1
group.save

gu = RoleMapping.new
gu.user_id = user.id
gu.group_id = group.id
gu.role = 'umsc'
gu.save

user = User.new
user.login_name = 'aoki'
user.password = 'doraemon'
user.full_name = 'Kentaro AOKI'
user.save

gu = RoleMapping.new
gu.user_id = user.id
gu.group_id = group.id
gu.role = 'umsc'
gu.save


survey = Survey.new
survey.survey_name = "沖縄県新型インフルエンザ小児医療情報ネットワーク事業"
survey.group_id = group.id
survey.status = 1
survey.report_header = '
<html>
<p>様式２　沖縄県新型インフルエンザ小児医療情報ネットワーク事業</p>
<p>県医師会御中</p>
<p>%%YEAR%%年%%MONTH%%月%%DAY%%日</p>
<p>沖縄県福祉保健部医務課</p>
<table border=1 style="font-size:12; border:2px #000000 solid; border-spacing:0; border-collapse: collapse;">
<tbody>
<tr>
<td rowspan=3>No</td>
<td rowspan=3>医療機関名（報告日時）</td>
<td colspan=3>小児のPICU・ICU管理症例数</td>
<td colspan=2>成人ICU管理症例数</td>
<td rowspan=3>小児重症患者受入の可否※</td>
<td rowspan=3>小児科病棟で管理中の長期人工呼吸器ケア症例数</td>
<td rowspan=3>備考(当直など）</td>
</tr>
<tr>
<td rowspan=2>新型インフルエンザ症例数</td>
<td colspan=2>新型インフルエンザ以外の症例数</td>
<td rowspan=2>新型インフルエンザ症例数</td>
<td rowspan=2>新型インフルエンザ以外の症例数</td>
</tr>
<tr>
<td>人工呼吸器使用例</td>
<td>人工呼吸器未使用例</td>
</tr>
<!-- Begin report data -->
'
survey.report_footer = '
<!-- End report data -->
</tbody>
</table>
<p>※小児重症患者受入の可否欄は（○：対応可能、△：相談可、×：対応困難）</p>
<p>連絡先 沖縄県医務課　糸数 電話：098-866-2169 ＦＡＸ：098-866-2714</p>
</html>
'
survey.save

SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'accept_child', :ocr_name_full => '小児重症患者受入可否', :view_order => 60, :data_type => 'rating').save
SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'flu_adult', :ocr_name_full => '成人の新型インフルエンザ', :view_order => 40, :data_type => 'number').save
SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'no_ventilator_child', :ocr_name_full => '小児の新型インフルエンザ以外・人工呼吸器未使用', :view_order => 30, :data_type => 'number').save
SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'remarks', :ocr_name_full => '備考', :view_order => 80, :data_type => 'image').save
SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'controled_child', :ocr_name_full => '小児科病棟で管理中の長期人工呼吸器ケア症例数', :view_order => 70, :data_type => 'number').save
SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'flu_child', :ocr_name_full => '小児の新型インフルエンザ', :view_order => 10, :data_type => 'number').save
SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'other_adult', :ocr_name_full => '成人の新型インフルエンザ以外', :view_order => 50, :data_type => 'number').save
SurveyProperty.new(:survey_id => survey.id, :ocr_name => 'ventilator_child', :ocr_name_full => '小児の新型インフルエンザ以外・人工呼吸器使用', :view_order => 20, :data_type => 'number').save

sheet = Sheet.new
sheet.sheet_code = SHEET_CODE1
sheet.sheet_name = "Version1"
sheet.survey_id = survey.id
sheet.block_width = 20
sheet.block_height = 12
sheet.status = 1
sheet.save

SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('accept_child').id, :position_x => '12', :position_y => '9', :colspan => 2, :sheet_id => sheet.id).save
SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('flu_adult').id, :position_x => '8', :position_y => '9', :colspan => 2, :sheet_id => sheet.id).save
SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('no_ventilator_child').id, :position_x => '6', :position_y => '9', :colspan => 2, :sheet_id => sheet.id).save
SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('remarks').id, :position_x => '16', :position_y => '9', :colspan => 4, :sheet_id => sheet.id).save
SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('controled_child').id, :position_x => '14', :position_y => '9', :colspan => 2, :sheet_id => sheet.id).save
SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('flu_child').id, :position_x => '2', :position_y => '9', :colspan => 2, :sheet_id => sheet.id).save
SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('other_adult').id, :position_x => '10', :position_y => '9', :colspan => 2, :sheet_id => sheet.id).save
SheetProperty.new(:survey_property_id => SurveyProperty.find_by_ocr_name('ventilator_child').id, :position_x => '4', :position_y => '9', :colspan => 2, :sheet_id => sheet.id).save

cands = []
cands << ['00001','沖縄県医務課','098-866-2169','098-866-2714','s']
cands << ['00002','県立北部病院','098-052-2719','0980-54-2298','sr']
cands << ['00004','中頭病院','098-939-1300','098-937-8699','sr']
cands << ['00005','県立中部病院','098-973-4111','098-973-2703','sr']
cands << ['00020','琉球大学付属病院','895-3331','895-3331','sr']
cands << ['00022','那覇市立病院','884-5111','884-5111','sr']
cands << ['00024','沖縄協同病院','853-1200','853-1200','sr']
cands << ['00025','沖縄赤十字病院','853-3134','853-3134','sr']
cands << ['00040','県立南部医療センター ・こども医療センター','888-0123','888-0123','sr']
cands.each do |cand|
  candidate = Candidate.new
  candidate.candidate_code = cand[0]
  candidate.candidate_name = cand[1]
  candidate.group_id = group.id
  candidate.tel_number = cand[2]
  candidate.fax_number = cand[3]
  candidate.save
  survey_candidate = SurveyCandidate.new
  survey_candidate.survey_id = survey.id
  survey_candidate.candidate_id = candidate.id
  survey_candidate.role = cand[4]
  survey_candidate.save
end

Process.exit

answer_sheet = AnswerSheet.new
answer_sheet.date = "20100209135005"
answer_sheet.sheet_id = sheet.id
answer_sheet.candidate_id = candidate.id
answer_sheet.sender_number = "0987777777"
answer_sheet.receiver_number = "0399999999"
answer_sheet.sheet_image = "path/to/image1"
answer_sheet.need_check = TRUE
props = []
props << ['no_ventilator_child','17','R0471518987/S0368931064/20100208135005/blockImg-no_ventilator_child.png']
props << ['flu_adult','11','R0471518987/S0368931064/20100208135005/blockImg-flu_adult.png']
props << ['accept_child','1','R0471518987/S0368931064/20100208135005/blockImg-accept_child.png']
props << ['controled_child','7','R0471518987/S0368931064/20100208135005/blockImg-controled_child.png']
props << ['flu_child','4','R0471518987/S0368931064/20100208135005/blockImg-flu_child.png']
props << ['remarks','','R0471518987/S0368931064/20100208135005/blockImg-remarks.png']
props << ['other_adult','8','R0471518987/S0368931064/20100208135005/blockImg-other_adult.png']
props << ['ventilator_child','9','R0471518987/S0368931064/20100208135005/blockImg-ventilator_child.png']
props.each do |prop|
  answer_sheet_property = AnswerSheetProperty.new
  answer_sheet_property.ocr_name = prop[0]
  answer_sheet_property.ocr_value = prop[1]
  answer_sheet_property.ocr_image = prop[2]
  answer_sheet.answer_sheet_properties << answer_sheet_property
end
answer_sheet.save

answer_sheet = AnswerSheet.new
answer_sheet.date = "20100209153000"
answer_sheet.sheet_id = sheet.id
answer_sheet.candidate_id = candidate.id
answer_sheet.sender_number = "0987777777"
answer_sheet.receiver_number = "0399999999"
answer_sheet.sheet_image = "path/to/image2"
props = []
props << ['no_ventilator_child','17','R0471518987/S0368931064/20100208135005/blockImg-no_ventilator_child.png']
props << ['flu_adult','11','R0471518987/S0368931064/20100208135005/blockImg-flu_adult.png']
props << ['accept_child','1','R0471518987/S0368931064/20100208135005/blockImg-accept_child.png']
props << ['controled_child','7','R0471518987/S0368931064/20100208135005/blockImg-controled_child.png']
props << ['flu_child','4','R0471518987/S0368931064/20100208135005/blockImg-flu_child.png']
props << ['remarks','','R0471518987/S0368931064/20100208135005/blockImg-remarks.png']
props << ['other_adult','8','R0471518987/S0368931064/20100208135005/blockImg-other_adult.png']
props << ['ventilator_child','9','R0471518987/S0368931064/20100208135005/blockImg-ventilator_child.png']
props.each do |prop|
  answer_sheet_property = AnswerSheetProperty.new
  answer_sheet_property.ocr_name = prop[0]
  answer_sheet_property.ocr_value = prop[1]
  answer_sheet_property.ocr_image = prop[2]
  answer_sheet.answer_sheet_properties << answer_sheet_property
end
answer_sheet.save

