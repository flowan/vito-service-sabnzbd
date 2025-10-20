sudo apt-get update -y

sudo systemctl stop sabnzbdplus

sudo apt-get install software-properties-common -y
sudo add-apt-repository multiverse -y
sudo add-apt-repository universe -y
sudo add-apt-repository ppa:jcfp/nobetas -y
sudo add-apt-repository ppa:jcfp/sab-addons -y
sudo apt-get update -y && sudo apt-get dist-upgrade -y
sudo apt-get install sabnzbdplus par2-turbo -y

sudo sed -i 's/USER=.*/USER=vito/g' /etc/default/sabnzbdplus

sudo systemctl -q daemon-reload
sudo systemctl enable --now -q sabnzbdplus
sudo systemctl restart sabnzbdplus

sleep 3

sudo sed -i 's/inet_exposure = ./inet_exposure = {{ $accessLevel }}/g' /home/vito/.sabnzbd/sabnzbd.ini
sudo sed -i 's/host = .*/host = 0.0.0.0/g' /home/vito/.sabnzbd/sabnzbd.ini
sudo sed -i 's/port = .*/port = {{ $port }}/g' /home/vito/.sabnzbd/sabnzbd.ini
sudo sed -i 's/username = .*/username = {{ $username }}/g' /home/vito/.sabnzbd/sabnzbd.ini
sudo sed -i 's/password = .*/password = {{ $password }}/g' /home/vito/.sabnzbd/sabnzbd.ini
sudo sed -i 's/download_dir = .*/download_dir = \/home\/vito\/media\/downloads\/incomplete/g' /home/vito/.sabnzbd/sabnzbd.ini
sudo sed -i 's/complete_dir = .*/complete_dir = \/home\/vito\/media\/downloads\/complete/g' /home/vito/.sabnzbd/sabnzbd.ini

sudo systemctl restart sabnzbdplus

sudo mkdir -p /home/vito/media
sudo mkdir -p /home/vito/media/downloads
sudo mkdir -p /home/vito/media/downloads/complete
sudo mkdir -p /home/vito/media/downloads/incomplete
sudo chown vito:media /home/vito/media
sudo chown vito:media /home/vito/media/downloads
sudo chown vito:media /home/vito/media/downloads/complete
sudo chown vito:media /home/vito/media/downloads/incomplete
sudo chmod 775 /home/vito/media
sudo chmod 775 /home/vito/media/downloads
sudo chmod 775 /home/vito/media/downloads/complete
sudo chmod 775 /home/vito/media/downloads/incomplete
