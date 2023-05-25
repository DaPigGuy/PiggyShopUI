# PiggyShopUI [![Poggit-CI](https://poggit.pmmp.io/shield.dl/PiggyShopUI)](https://poggit.pmmp.io/p/PiggyShopUI) [![Discord](https://img.shields.io/discord/330850307607363585?logo=discord)](https://discord.gg/qmnDsSD)

PiggyShopUI is an open-sourced plugin using [libFormAPI](https://github.com/jojoe77777/FormAPI) to create form shops.

## Prerequisites

* Basic knowledge on how to install plugins from Poggit Releases and/or Poggit CI
* PMMP 4.21.0+

## Supported Economy Providers

* [EconomyAPI](https://poggit.pmmp.io/p/EconomyAPI) by onebone/poggit-orphanage
* [BedrockEconomy](https://poggit.pmmp.io/p/BedrockEconomy) by cooldogedev
* Experience (PMMP)

## Installation & Setup

1. Install the plugin from Poggit.
2. (Optional) Setup your economy provider. Change `economy.provider` to the name of the economy plugin being used,
   or `xp` for PMMP Player EXP.
3. Start your server.
4. The command `/shop edit` opens up the shop editor menu. Run it.
5. PiggyShopUI separates individual shop entries by categories. Create a category.
6. PiggyShopUI allows you to add shop items from your inventory. Hold the items you plan on selling in your hand.
7. Open the shop editor menu and select a category to edit. This will open up the shop category editor menu.
8. Add an item to the category.
9. Repeat w/ other items & categories.
10. You're done! No restarts are necessary.

## Commands

| Command            | Description                           | Permissions                     | Aliases |
|--------------------|---------------------------------------|---------------------------------|---------|
| `/shop edit`       | Opens the shop editor menu            | `piggyshopui.command.shop.edit` | N/A     |
| `/shop`            | Opens the shop                        | `piggyshopui.command.shop.use`  | N/A     |
| `/shop [category]` | Opens a specific category in the shop | `piggyshopui.command.shop.use`  | N/A     |

## Permissions

| Permissions                       | Description                              | Default |
|-----------------------------------|------------------------------------------|---------|
| `piggyshopui`                     | Allows usage of all PiggyShopUI features | `false` |
| `piggyshopui.category.{CATEGORY}` | Allows access to private categories      | `op`    |
| `piggyshopui.command`             | Allow usage of all PiggyShopUI commands  | `op`    |
| `piggyshopui.command.shop`        | Allow usage of the /shop commands        | `op`    |
| `piggyshopui.command.shop.edit`   | Allow usage of the /shop edit subcommand | `op`    |
| `piggyshopui.command.shop.use`    | Allow usage of the /shop command         | `true`  |

## Issue Reporting

* If you experience an unexpected non-crash behavior with PiggyShopUI,
  click [here](https://github.com/DaPigGuy/PiggyShopUI/issues/new?assignees=DaPigGuy&labels=bug&template=bug_report.md&title=)
  .
* If you experience a crash in PiggyShopUI,
  click [here](https://github.com/DaPigGuy/PiggyShopUI/issues/new?assignees=DaPigGuy&labels=bug&template=crash.md&title=)
  .
* If you would like to suggest a feature to be added to PiggyShopUI,
  click [here](https://github.com/DaPigGuy/PiggyShopUI/issues/new?assignees=DaPigGuy&labels=suggestion&template=suggestion.md&title=)
  .
* If you require support, please join our discord server [here](https://discord.gg/qmnDsSD).
* Do not file any issues related to outdated API version; we will resolve such issues as soon as possible.
* We do not support any spoons of PocketMine-MP. Anything to do with spoons (Issues or PRs) will be ignored.
    * This includes plugins that modify PocketMine-MP's behavior directly, such as TeaSpoon.

## License

```
   Copyright 2018 DaPigGuy

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
