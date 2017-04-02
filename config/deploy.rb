# config valid only for current version of Capistrano
lock '3.6.0'

set :application, 'magento1_extension'
set :repo_url, 'git@bitbucket.org:strakertech/magento-plugin.git'

set :branch, ENV['BRANCH'] || "master"
set :deploy_to, '/mnt/data/apps/php/magento1-ext'

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
# append :linked_files, 'config/database.yml', 'config/secrets.yml'

# Default value for linked_dirs is []
# append :linked_dirs, 'log', 'tmp/pids', 'tmp/cache', 'tmp/sockets', 'public/system'

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
# set :keep_releases, 5

namespace :deploy do

	desc "Dev env git pull "
		task :gitpull do
			on roles :dev do
      execute "cd /mnt/data/apps/php/mg1-dev1 && git pull"
			execute "cd /mnt/data/apps/php/mg1-dev2 && git pull"
		end
	end
	after "deploy:publishing", "gitpull"

  desc "UAT env git pull "
    task :gitpull do
      on roles :uat do
      execute "cd /mnt/data/apps/php/mg1-uat1 && git pull"
      execute "cd /mnt/data/apps/php/mg1-uat2 && git pull"
    end
  end
  after "deploy:publishing", "gitpull"

  desc "Production env git pull "
    task :gitpull do
      on roles :demo do
      execute "cd /mnt/data/apps/php/mg1-demo1 && git pull"
      execute "cd /mnt/data/apps/php/mg1-demo2 && git pull"
    end
  end
  after "deploy:publishing", "gitpull"

  desc "Restarting php5-fpm to clear cache"
  task :fpmreload do
    on roles :all do
      execute "service php5-fpm restart"
    end
  end
  after "deploy:published", "fpmreload"

  after :restart, :clear_cache do
    on roles(:web), in: :groups, limit: 3, wait: 10 do
      # Here we can do anything such as:
      # within release_path do
      #   execute :rake, 'cache:clear'
      # end
    end
  end

end