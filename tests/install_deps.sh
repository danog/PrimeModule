#!/bin/bash

sudo apt-get install build-essential && git clone https://github.com/danog/PrimeModule-ext && cd PrimeModule-ext && make -j$(nproc) && sudo make install

exit 0
