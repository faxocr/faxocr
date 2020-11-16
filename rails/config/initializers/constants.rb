FAXOCR_DIR = File.expand_path('../../../..', __FILE__)
FAXOCR_BIN_DIR = File.join(FAXOCR_DIR, 'bin')
FAXOCR_ETC_DIR = File.join(FAXOCR_DIR, 'etc')
FAXOCR_FAXSYSTEM_LOG_DIR = File.join(FAXOCR_DIR, 'Faxsystem/Log')

SHEETREADER_ANALYZED_DIR = File.join(FAXOCR_DIR, 'Faxsystem/analyzedimage')
SHEETREADER_CONFIG_DIR = File.join(FAXOCR_DIR, 'faxocr_config/recognize_sheetreader')

MAIL_QUEUE_DIR = File.join(FAXOCR_DIR, 'Maildir/new')

RAILS_DIR = File.join(FAXOCR_DIR, 'rails')
RAILS_FILES_DIR = File.join(RAILS_DIR, 'files')
