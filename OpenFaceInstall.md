sudo apt-get update
sudo apt-get install build-essential
sudo apt-get install g++-8

sudo apt-get install cmake

sudo apt-get install git libgtk2.0-dev pkg-config libavcodec-dev libavformat-dev libswscale-dev

sudo apt-get install python-dev python-numpy libtbb2 libtbb-dev libjpeg-dev libpng-dev libtiff-dev libdc1394-22-dev

wget https://github.com/opencv/opencv/archive/4.1.0.zip

sudo unzip 4.1.0.zip
cd opencv-4.1.0
mkdir build
cd build

cmake -D CMAKE_BUILD_TYPE=RELEASE -D CMAKE_INSTALL_PREFIX=/usr/local -D BUILD_TIFF=ON -D WITH_TBB=ON ..
make -j2
sudo make install

wget http://dlib.net/files/dlib-19.13.tar.bz2
tar xf dlib-19.13.tar.bz2
cd dlib-19.13
mkdir build
cd build
cmake ..
cmake --build . --config Release
sudo make install
sudo ldconfig
cd ../..

sudo apt-get install libboost-all-dev

git clone https://github.com/TadasBaltrusaitis/OpenFace.git

cd OpenFace
mkdir build
cd build

cmake -D CMAKE_CXX_COMPILER=g++-8 -D CMAKE_C_COMPILER=gcc-8 -D CMAKE_BUILD_TYPE=RELEASE ..
make

# Download the models
# Note, you might get errors due to it being someones gmail storage. You can still manually download the files.
sudo bash ./download_models.sh