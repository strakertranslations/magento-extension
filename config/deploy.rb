# config valid only for current version of Capistrano
lock '3.6.0'

set :application, 'straker_magento2_extension'
set :repo_url, 'git@bitbucket.org:strakertranslations/magento2.git'

# Default branch is :master
# ask :branch, `git rev-parse --abbrev-ref HEAD`.chomp
set :branch, ENV['BRANCH'] || "master"
# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, '/mnt/data/apps/php/magento2-ext'

# Default branch is :master
# ask :branch, `git rev-parse --abbrev-ref HEAD`.chomp

# Default deploy_to directory is /var/www/my_app_name
# set :deploy_to, '/var/www/my_app_name'

# Default value for :scm is :git
# set :scm, :git

# Default value for :format is :airbrussh.
# set :format, :airbrussh

# You can configure the Airbrussh format using :format_options.
# These are the defaults.
# set :format_options, command_output: true, log_file: 'log/capistrano.log', color: :auto, truncate: :auto

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
# set :linked_files, fetch(:linked_files, []).push('config/database.yml', 'config/secrets.yml')

# Default value for linked_dirs is []
# set :linked_dirs, fetch(:linked_dirs, []).push('log', 'tmp/pids', 'tmp/cache', 'tmp/sockets', 'public/system')

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
# set :keep_releases, 5

namespace :deploy do

  # Dev environment
  desc "Magento2 git"
  task :magento2_git do
    on roles :dev do
      execute "cd /mnt/data/apps/php/mg2-dev1/app/code/Straker/EasyTranslationPlatform && git pull"
      execute "cd /mnt/data/apps/php/mg2-dev2/app/code/Straker/EasyTranslationPlatform && git pull"
    end
  end
  after "deploy:publishing", "magento2_git"

  desc "Magento2 setup"
  task :magento2_ownership do
    on roles :dev do
      execute "cd /mnt/data/apps/php/mg2-dev1/bin; php magento setup:upgrade"
      execute "cd /mnt/data/apps/php/mg2-dev2/bin; php magento setup:upgrade"
    end
  end
  after "deploy:publishing", "magento2_ownership"

  desc "Magento2 ownership"
  task :magento2_setup do
    on roles :dev do
      execute "cd /mnt/data/apps/php/mg2-dev1 && chown -R www-data:www-data ."
      execute "cd /mnt/data/apps/php/mg2-dev2 && chown -R www-data:www-data ."
    end
  end
  after "deploy:finishing", "magento2_setup"

  # UAT environment
  desc "Magento2 git"
  task :magento2_git do
    on roles :uat do
      execute "cd /mnt/data/apps/php/mg2-uat1/app/code/Straker/EasyTranslationPlatform && git pull"
      execute "cd /mnt/data/apps/php/mg2-uat2/app/code/Straker/EasyTranslationPlatform && git pull"
    end
  end
  after "deploy:publishing", "magento2_git"

  desc "Magento2 setup"
  task :magento2_ownership do
    on roles :uat do
      execute "cd /mnt/data/apps/php/mg2-uat1/bin; php magento setup:upgrade"
      execute "cd /mnt/data/apps/php/mg2-uat2/bin; php magento setup:upgrade"
    end
  end
  after "deploy:publishing", "magento2_ownership"

  desc "Magento2 ownership"
  task :magento2_setup do
    on roles :uat do
      execute "cd /mnt/data/apps/php/mg2-uat1 && chown -R www-data:www-data ."
      execute "cd /mnt/data/apps/php/mg2-uat2 && chown -R www-data:www-data ."
    end
  end
  after "deploy:finishing", "magento2_setup"


  desc "Restarting php5-fpm to clear cache"
  task :fpmreload do
    on roles :all do
      execute "service php5-fpm restart"
    end
  end
  after "deploy:finished", "fpmreload"

end
