# Update Aptitude with more
# recent repository information
apt-get -qq update --fix-missing

# Setting the RUNLEVEL to 1 stops beanstalkd from
# attempting to start up at install time - we need
# to configure it first or it whines.
RUNLEVEL=1 apt-get -qq -y install beanstalkd php5-cli

# Configure beanstalkd
cat > /etc/default/beanstalkd <<CONFIG
BEANSTALKD_LISTEN_ADDR=127.0.0.1
BEANSTALKD_LISTEN_PORT=11300
DAEMON_OPTS="-l \$BEANSTALKD_LISTEN_ADDR -p \$BEANSTALKD_LISTEN_PORT"
START=yes
CONFIG

# Start the beanstalkd service
service beanstalkd start