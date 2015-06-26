# Be sure to restart your server when you modify this file.

# Your secret key for verifying cookie session data integrity.
# If you change this key, all old sessions will become invalid!
# Make sure the secret is at least 30 characters and all random, 
# no regular words or you'll be exposed to dictionary attacks.
ActionController::Base.session = {
  :key         => '_linedsurvey_session',
  :secret      => 'e69d6be0e02c42395a59e70f594f2e339363f67bc9778608d55268d7c0bcdafa6347770dfe11175c88dedc02fda0dcf9a73d4914cd76672309184c23cfe016e4'
}

# Use the database for sessions instead of the cookie-based default,
# which shouldn't be used to store highly confidential information
# (create the session table with "rails generate session_migration")
# ActionController::Base.session_store = :active_record_store
