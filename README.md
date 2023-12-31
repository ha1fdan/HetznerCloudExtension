[![Contributors][contributors-shield]][contributors-url]
[![Stars][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]

# Hetzner Cloud Extension for [paymenter.org](https://github.com/Paymenter/Paymenter)
> Note: This extension is in its early stages and may contain bugs.

The extension uses Hetzners API to create Cloud Servers automatically.  The extension shows information about your server, like IPv4, IPv6 & temporarily root password for SSH.

Power controls for Starting, Stopping and Rebooting the server. You can also Force Stop the server or Reset the root Password if you get locked out. And if everything goes wrong you can Rebuild the OS. You can also set the Reverse DNS entry if needed.

You can also see metrics for CPU, Disk and Network usage.
<br/>Supports configurable options for: `location` and `image`.

If you want to implement new features check out Hetzner Cloud Docs: https://docs.hetzner.cloud/ & make a pull request ❤️

Get the extension at: https://market.paymenter.org/extensions/27/HetznerCloud 🎉
<br/>For support or feature requests please message [@ha1fdan](https://discord.gg/n8hpbek59q) on discord.

---

## Features
- [x] Configurable settings for location & os-image
- [x] Shows IPv4 & IPv6
- [x] Shows Temporarily root Password
- [x] Reset root Password
- [x] Start, Stop, Force Stop & Reset Server
- [x] Rebuild OS, aka. reinstall os
- [x] Set Reverse DNS
- [x] CPU, Disk & Network Metrics graphs
- [ ] VNC Console

---

## Screenshots
![configurableOptions](https://ha1fdan.xyz/HetznerCloudExtension/screenshots/1.png)
<p>Support for configurable options (client side)</p>

![productOrderPage](https://ha1fdan.xyz/HetznerCloudExtension/screenshots/2.png)
<p>Server Information (client side)</p>

![productOrderPage](https://ha1fdan.xyz/HetznerCloudExtension/screenshots/3.png)
<p>Power Control & Status (client side)</p>

![productExtensionConfig](https://ha1fdan.xyz/HetznerCloudExtension/screenshots/4.png)
<p>Metrics for cpu, disk and network usage (client side)</p>

![extensionSettings](https://ha1fdan.xyz/HetznerCloudExtension/screenshots/5.png)
<p>Example of config for a product (admin side)</p>

![extensionSettings](https://ha1fdan.xyz/HetznerCloudExtension/screenshots/6.png)
<p>Settings for extension (admin side)</p>


[contributors-shield]: https://img.shields.io/github/contributors/ha1fdan/HetznerCloudExtension.svg?style=for-the-badge
[contributors-url]: https://github.com/ha1fdan/HetznerCloudExtension/graphs/contributors
[stars-shield]: https://img.shields.io/github/stars/ha1fdan/HetznerCloudExtension.svg?style=for-the-badge
[stars-url]: https://github.com/ha1fdan/HetznerCloudExtension/stargazers
[issues-shield]: https://img.shields.io/github/issues/ha1fdan/HetznerCloudExtension.svg?style=for-the-badge
[issues-url]: https://github.com/ha1fdan/HetznerCloudExtension/issues
[license-shield]: https://img.shields.io/github/license/ha1fdan/HetznerCloudExtension.svg?style=for-the-badge
[license-url]: https://github.com/ha1fdan/HetznerCloudExtension/blob/master/LICENSE
