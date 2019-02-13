# Wake-And-Bake
API for turning on your PC with any server -- even a Raspberry Pi!

# Compatibility

- Compatible with web hooks
- Boots multiple devices
- Compatible Google Assistant! "Hey Google, turn on my office computer" and or "Hey Google, turn on my bedroom computer", etc.

It will turn on any wired ethernet based PC that supports [Wake-On-Lan Packets](https://en.wikipedia.org/wiki/Wake-on-LAN). You will have to make sure this is turned on in your BIOS/Windows. This of course means, you can't turn on wireless devices such as laptops.

Unfortunately the shutdown feature isn't compatible with Windows as it only uses a simple SSH script to remotely shutdown the PC via shutdown API call.

# Installation

For this project, I personally used a Raspberry Pi using an [Ubuntu Classic Server](https://ubuntu-pi-flavour-maker.org/download/) image.

However, as long as you can install [Docker](https://docs.docker.com/install/#supported-platforms) this should work on everything that runs Docker.

1. Install Docker and make sure to add yourself to the docker group. `sudo usermod -aG docker $USER` then reboot. 
2. Install git
2. [Install Crowdr](https://github.com/polonskiy/crowdr#user-content-installation) and make sure it's executable. `sudo chmod +x /usr/local/bin/crowdr`
3. `git clone https://github.com/CRTX/Wake-And-Bake.git`
4. `cd Wake-And-Bake/server/php`
5. Run the command `docker run -it --rm --net=host -v $HOME:$HOME -e COMPOSER_HOME="$HOME/.composer" -u $UID -w `pwd` composer:latest install`
6. `cd ../../`
8. `crowdr build`
7. `crowdr run`

That's it! The API server is up and running!

Now we just need to add the hosts you want to control.
# Configuration

You should now be in the root `Wake-And-Bake` directory from the last installation step.

1. To add as many hosts as you want, run `docker exec -it wakenbake-fpm console app:add:host`

    Follow the steps, it'll ask you the necessary information to wake up and shutdown your PC.
    
    **Note (again)**: You can't wake (turn on) wireless devices such as a laptop. Wake-On-Lan is ethernet only and Windows shutdown is not supported at the moment.

2. For shutdown functionality, generate a SSH key for your device that the API and other tools will be using.
   
   `docker exec -it wakenbake-fpm console app:ssh:generate`

3. Next, run `docker exec -it wakenbake-fpm console app:ssh:key-to-hosts`. This will copy your public ssh key to all the hosts you configured in the first configuration step.

4. At last, run `crowdr run` one last time and you're ready to make API calls!

# Google Assistant

I would like to post how to get Google Assistant to work from here but this README is way longer than I originally thought it'd be.

I'll do a short explanation for now and maybe I'll add a more detailed guide later.

1. Create an account at https://ifttt.com (sign in with the google account that you'll be using assistant with).

2. Go to https://ifttt.com/create.

3. Search in the texbox for google assistant. Then select "Say a phrase with a text ingredient".

4. Fill out the words you'd like to use to turn on your PC. I personally entered in the box "turn on computer $". Without the quotation marks. Go to the bottom and click save.

5. Select the "that" button and search for Webhooks.

6. Enter exactly `http://x.x.x.x/api/boot/{{TextField}}` except for x.x.x.x which is your ip address. (Make sure you have port forwarding on your router to the API server's IP address on port 80)

7. Select "GET" in the dropdown.

8. Click save and you're done!

Congrats, if you followed everything correctly you should be able to say. "Hey Google, turn on my computer X". Where X is the alias you chose in step #1 of the configuration section. :)

If it doesn't work, then double check inside the auto-generated `wakeHosts.yaml`. You may have a typo somewhere there too.
If it still doesn't work, I'll add step-by-step images later if I feel like it :P Good luck!
