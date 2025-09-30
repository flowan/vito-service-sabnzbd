sudo systemctl stop sabnzbdplus
sudo systemctl disable sabnzbdplus

sudo apt-get purge sabnzbdplus -y

sudo rm -rf /home/vito/.sabnzbd
