# Install
```
# apt install exim4
```

# Configure
```
# dpkg-reconfigure exim4.config
```
The autogenerate config file at `/etc/exim4/update-exim4.conf.conf` should look like this after the wizard:
```
dc_eximconfig_configtype='internet'
dc_other_hostnames=''
dc_local_interfaces=''
dc_readhost=''
dc_relay_domains=''
dc_minimaldns='false'
dc_relay_nets=''
dc_smarthost=''
CFILEMODE='644'
dc_use_split_config='true'
dc_hide_mailname=''
dc_mailname_in_oh='true'
dc_localdelivery='maildir_home'
```

# Generate certificate for TLS
```
# /usr/share/doc/exim4-base/examples/exim-gencert
```
Files will be stored at:
```
/etc/exim4/exim.crt
/etc/exim4/exim.key
```

# Create user credentials
```
# /usr/share/doc/exim4/examples/exim-adduser
```
This will create a file at `/etc/exim4/passwd`. Protect it (VERY IMPORTANT):
```
sudo chown root:Debian-exim /etc/exim4/passwd
sudo chmod 640 /etc/exim4/passwd
```

# Enable TLS
```
# echo "MAIN_TLS_ENABLE = yes" > /etc/exim4/conf.d/main/00_local_settings
```

# Enable AUTH
Uncomment following lines in /etc/exim4/conf.d/auth/30_exim4-config_examples
```
plain_server:
   driver = plaintext
   public_name = PLAIN
   server_condition = "${if crypteq{$auth3}{${extract{1}{:}{${lookup{$auth2}lsearch{CONFDIR/passwd}{$value}{*:*}}}}}{1}{0}}"
   server_set_id = $auth2
   server_prompts = :
   .ifndef AUTH_SERVER_ALLOW_NOTLS_PASSWORDS
   server_advertise_condition = ${if eq{$tls_cipher}{}{}{*}}
   .endif

login_server:
   driver = plaintext
   public_name = LOGIN
   server_prompts = "Username:: : Password::"
   server_condition = "${if crypteq{$auth2}{${extract{1}{:}{${lookup{$auth1}lsearch{CONFDIR/passwd}{$value}{*:*}}}}}{1}{0}}"
   server_set_id = $auth1
   .ifndef AUTH_SERVER_ALLOW_NOTLS_PASSWORDS
   server_advertise_condition = ${if eq{$tls_cipher}{}{}{*}}
   .endif  
```

# Listen on ports other than 25
Modify this line in `/etc/default/exim4`
```
SMTPLISTENEROPTIONS='-oX 587:465:25 -oP /var/run/exim4/exim.pid'
```

# Apply configuration changes and restart
```
# update-exim4.conf
# /etc/init.d/exim4 restart  
```
