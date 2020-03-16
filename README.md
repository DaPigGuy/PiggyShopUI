# PiggyShopUI [![Poggit-CI](https://poggit.pmmp.io/ci.badge/DaPigGuy/PiggyShopUI/PiggyShopUI/master)](https://poggit.pmmp.io/ci/DaPigGuy/PiggyShopUI/~) 

PiggyShopUI is an open-sourced plugin using [libFormAPI](https://github.com/jojoe77777/FormAPI) to create form shops.

## Supported Economy Plugins
* [EconomyAPI](https://github.com/onebone/EconomyS/tree/3.x/EconomyAPI) by onebone
* [MultiEconomy](https://github.com/TwistedAsylumMC/MultiEconomy) by TwistedAsylumMC
* Or, you can use player experience as a monetary value.

## Commands
| Command | Description | Permissions | Aliases
| --- | --- | --- | --- |
| `/shop edit` | Opens the shop editor menu | `piggyshopui.command.shop.edit` | N/A |
| `/shop` | Opens the shop | `piggyshopui.command.shop.use` | N/A |
| `/shop [category]` | Opens a specific category in the shop | `piggyshopui.command.shop.use` | N/A |

## Permissions
| Permissions | Description | Default |
| --- | --- | --- |
| `piggyshopui` | Allows usage of all PiggyShopUI features | `false` |
| `piggyshopui.command` | Allow usage of all PiggyShopUI commands | `op` |
| `piggyshopui.command.shop` | Allow usage of the /shop commands | `op` |
| `piggyshopui.command.shop.edit` | Allow usage of the /shop edit subcommand | `op` |
| `piggyshopui.command.shop.use` | Allow usage of the /shop command | `true` |
| `piggyshopui.category.{CATEGORY}` | Allows access to private categories | `op` |

## Issue Reporting
* If you experience an unexpected non-crash behavior with PiggyShopUI, click [here](https://github.com/DaPigGuy/PiggyShopUI/issues/new?assignees=DaPigGuy&labels=bug&template=bug_report.md&title=).
* If you experience a crash in PiggyShopUI, click [here](https://github.com/DaPigGuy/PiggyShopUI/issues/new?assignees=DaPigGuy&labels=bug&template=crash.md&title=).
* If you would like to suggest a feature to be added to PiggyShopUI, click [here](https://github.com/DaPigGuy/PiggyShopUI/issues/new?assignees=DaPigGuy&labels=suggestion&template=suggestion.md&title=).
* If you require support, please join our discord server [here](https://discord.gg/qmnDsSD).
* Do not file any issues related to outdated API version; we will resolve such issues as soon as possible.
* We do not support any spoons of PocketMine-MP. Anything to do with spoons (Issues or PRs) will be ignored.
  * This includes plugins that modify PocketMine-MP's behavior directly, such as TeaSpoon.

## Information
* We do not support any spoons. Anything to do with spoons (Issues or PRs) will be ignored.
* We are using the following virions: [Commando](https://github.com/CortexPE/Commando), [libFormAPI](https://github.com/jojoe77777/FormAPI), and [libPiggyEconomy](https://github.com/DaPigGuy/libPiggyEconomy).
    * **You MUST use the pre-compiled phar from [Poggit-CI](https://poggit.pmmp.io/ci/DaPigGuy/PiggyShopUI/~) instead of GitHub.**
    * If you wish to run it via source, check out [DEVirion](https://github.com/poggit/devirion).
* Check out our [Discord Server](https://discord.gg/qmnDsSD) for additional plugin support.

## License
```
   Copyright 2017-2020 DaPigGuy

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.

```
