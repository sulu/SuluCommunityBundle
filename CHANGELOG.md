# Change Log

## [1.1.1](https://github.com/sulu/SuluCommunityBundle/tree/1.1.1) (2019-06-11)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/1.1.0...1.1.1)

**Implemented enhancements:**

- Tests should also be runnned with prefer-lowest [\#50](https://github.com/sulu/SuluCommunityBundle/issues/50)

**Fixed bugs:**

- Fix init command [\#114](https://github.com/sulu/SuluCommunityBundle/pull/114) ([alexander-schranz](https://github.com/alexander-schranz))

**Closed issues:**

- Notice: Undefined property: Sulu\Bundle\CommunityBundle\Command\InitCommand::$entityManager [\#115](https://github.com/sulu/SuluCommunityBundle/issues/115)

## [1.1.0](https://github.com/sulu/SuluCommunityBundle/tree/1.1.0) (2019-05-17)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/1.0.1...1.1.0)

**Implemented enhancements:**

- Simplify form types and fix compatibility to sulu 2 and symfony 4 [\#107](https://github.com/sulu/SuluCommunityBundle/pull/107) ([alexander-schranz](https://github.com/alexander-schranz))
- Add allow access of registration, password reset and password forget in example firewall [\#98](https://github.com/sulu/SuluCommunityBundle/pull/98) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix test cases with sulu 2.0, update to phpunit 6 and add branch alias [\#96](https://github.com/sulu/SuluCommunityBundle/pull/96) ([alexander-schranz](https://github.com/alexander-schranz))
- Add test configuration to installation guide [\#95](https://github.com/sulu/SuluCommunityBundle/pull/95) ([alexander-schranz](https://github.com/alexander-schranz))
- Improve webspace configuration documentation [\#91](https://github.com/sulu/SuluCommunityBundle/pull/91) ([alexander-schranz](https://github.com/alexander-schranz))
- Add missing documentation for asset install and generate translations [\#90](https://github.com/sulu/SuluCommunityBundle/pull/90) ([alexander-schranz](https://github.com/alexander-schranz))

**Fixed bugs:**

- Fix composer deprecation and upgrade ci to php 7.3 [\#108](https://github.com/sulu/SuluCommunityBundle/pull/108) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix profile without note for mysql 5.7 [\#103](https://github.com/sulu/SuluCommunityBundle/pull/103) ([alexander-schranz](https://github.com/alexander-schranz))
- Added contact documents upload and fixed profile without avatar [\#101](https://github.com/sulu/SuluCommunityBundle/pull/101) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix loading minified js files for production [\#99](https://github.com/sulu/SuluCommunityBundle/pull/99) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix compatibility with jms serializer 2, sulu 2 and mysql utf8mb4 [\#93](https://github.com/sulu/SuluCommunityBundle/pull/93) ([alexander-schranz](https://github.com/alexander-schranz))

**Merged pull requests:**

- Add 'useAttributeAsKey' to webspaces config node [\#113](https://github.com/sulu/SuluCommunityBundle/pull/113) ([wachterjohannes](https://github.com/wachterjohannes))
- Fix initializing of webspaces with same role name [\#112](https://github.com/sulu/SuluCommunityBundle/pull/112) ([nnatter](https://github.com/nnatter))
- Updated composer.json to support symfony 4 [\#106](https://github.com/sulu/SuluCommunityBundle/pull/106) ([wachterjohannes](https://github.com/wachterjohannes))
- Added address entity also to contact in completion form [\#105](https://github.com/sulu/SuluCommunityBundle/pull/105) ([alexander-schranz](https://github.com/alexander-schranz))
- Added registry for community-manager [\#104](https://github.com/sulu/SuluCommunityBundle/pull/104) ([wachterjohannes](https://github.com/wachterjohannes))
- Fixed use of moved contact-repository-interface [\#97](https://github.com/sulu/SuluCommunityBundle/pull/97) ([wachterjohannes](https://github.com/wachterjohannes))
- Improve documentation with a better hint for duplicated bundle registration [\#92](https://github.com/sulu/SuluCommunityBundle/pull/92) ([alexander-schranz](https://github.com/alexander-schranz))

## [1.0.1](https://github.com/sulu/SuluCommunityBundle/tree/1.0.1) (2018-03-08)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/1.0.0...1.0.1)

**Merged pull requests:**

- Document to allow fragments and login be accessable anonymously [\#89](https://github.com/sulu/SuluCommunityBundle/pull/89) ([martinlagler](https://github.com/martinlagler))
- Fix login embed example file [\#88](https://github.com/sulu/SuluCommunityBundle/pull/88) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix false firewall name and validate webspace in community admin [\#87](https://github.com/sulu/SuluCommunityBundle/pull/87) ([alexander-schranz](https://github.com/alexander-schranz))
- Use route name instead of path in security configuration [\#86](https://github.com/sulu/SuluCommunityBundle/pull/86) ([martinlagler](https://github.com/martinlagler))

## [1.0.0](https://github.com/sulu/SuluCommunityBundle/tree/1.0.0) (2018-02-13)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/1.0.0-RC3...1.0.0)

**Merged pull requests:**

- Add description to customization documentation [\#85](https://github.com/sulu/SuluCommunityBundle/pull/85) ([alexander-schranz](https://github.com/alexander-schranz))
- Allow webspaces with dash [\#84](https://github.com/sulu/SuluCommunityBundle/pull/84) ([alexander-schranz](https://github.com/alexander-schranz))
- Fallback to email address to from email address when not set [\#81](https://github.com/sulu/SuluCommunityBundle/pull/81) ([alexander-schranz](https://github.com/alexander-schranz))

## [1.0.0-RC3](https://github.com/sulu/SuluCommunityBundle/tree/1.0.0-RC3) (2017-12-11)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/1.0.0-RC2...1.0.0-RC3)

**Merged pull requests:**

- Add maintenance mode [\#80](https://github.com/sulu/SuluCommunityBundle/pull/80) ([alexander-schranz](https://github.com/alexander-schranz))

## [1.0.0-RC2](https://github.com/sulu/SuluCommunityBundle/tree/1.0.0-RC2) (2017-12-05)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/1.0.0-RC1...1.0.0-RC2)

**Implemented enhancements:**

- Document deactivate of client and proxy cache when use login-embed [\#40](https://github.com/sulu/SuluCommunityBundle/issues/40)
- Password reset on disabled user [\#28](https://github.com/sulu/SuluCommunityBundle/issues/28)

**Fixed bugs:**

- System added is not shown in the backend role edition [\#60](https://github.com/sulu/SuluCommunityBundle/issues/60)
- Add email when main email is written [\#46](https://github.com/sulu/SuluCommunityBundle/issues/46)
- Can't create a registration/profile form without an address and note [\#45](https://github.com/sulu/SuluCommunityBundle/issues/45)
- Form options not working [\#17](https://github.com/sulu/SuluCommunityBundle/issues/17)

**Merged pull requests:**

- Add bundle version to github template [\#79](https://github.com/sulu/SuluCommunityBundle/pull/79) ([alexander-schranz](https://github.com/alexander-schranz))
- Release 1.0.0-RC2 [\#78](https://github.com/sulu/SuluCommunityBundle/pull/78) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix form options parameter in configuration [\#77](https://github.com/sulu/SuluCommunityBundle/pull/77) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix shown systems in sulu role backend ui [\#76](https://github.com/sulu/SuluCommunityBundle/pull/76) ([alexander-schranz](https://github.com/alexander-schranz))
- confirm and enable user also with password reset email [\#75](https://github.com/sulu/SuluCommunityBundle/pull/75) ([alexander-schranz](https://github.com/alexander-schranz))
- Add documentation for client side cache headers [\#74](https://github.com/sulu/SuluCommunityBundle/pull/74) ([alexander-schranz](https://github.com/alexander-schranz))
- Create and update correct email entity for backend edit [\#73](https://github.com/sulu/SuluCommunityBundle/pull/73) ([alexander-schranz](https://github.com/alexander-schranz))
- Update documentation for esi preview error [\#72](https://github.com/sulu/SuluCommunityBundle/pull/72) ([alexander-schranz](https://github.com/alexander-schranz))

## [1.0.0-RC1](https://github.com/sulu/SuluCommunityBundle/tree/1.0.0-RC1) (2017-11-17)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/0.3.1...1.0.0-RC1)

**Closed issues:**

- I can not login with registred user [\#57](https://github.com/sulu/SuluCommunityBundle/issues/57)

**Merged pull requests:**

- Release 1.0.0-RC1 [\#71](https://github.com/sulu/SuluCommunityBundle/pull/71) ([alexander-schranz](https://github.com/alexander-schranz))
- Add phpstan to travis ci [\#70](https://github.com/sulu/SuluCommunityBundle/pull/70) ([alexander-schranz](https://github.com/alexander-schranz))
- Run tests with symfony 3 [\#69](https://github.com/sulu/SuluCommunityBundle/pull/69) ([alexander-schranz](https://github.com/alexander-schranz))
- Increase memory limit for tests and use yoda conditions for styleci changes [\#68](https://github.com/sulu/SuluCommunityBundle/pull/68) ([alexander-schranz](https://github.com/alexander-schranz))
- Bugfix/fix for sf3 incompatibel formtype properties [\#67](https://github.com/sulu/SuluCommunityBundle/pull/67) ([nwaelkens](https://github.com/nwaelkens))

## [0.3.1](https://github.com/sulu/SuluCommunityBundle/tree/0.3.1) (2017-09-04)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/0.3.0...0.3.1)

**Merged pull requests:**

- Fix minor registration doc typo [\#66](https://github.com/sulu/SuluCommunityBundle/pull/66) ([brieucthomas](https://github.com/brieucthomas))
- Removed class construction for sub-types [\#65](https://github.com/sulu/SuluCommunityBundle/pull/65) ([wachterjohannes](https://github.com/wachterjohannes))
- update webspace configuration use exist sulu\_admin.email parameter [\#62](https://github.com/sulu/SuluCommunityBundle/pull/62) ([alexander-schranz](https://github.com/alexander-schranz))
- fix documentation for login and logout config [\#61](https://github.com/sulu/SuluCommunityBundle/pull/61) ([alexander-schranz](https://github.com/alexander-schranz))

## [0.3.0](https://github.com/sulu/SuluCommunityBundle/tree/0.3.0) (2017-04-03)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/0.2.0...0.3.0)

**Closed issues:**

- Implement from and to name for emails [\#52](https://github.com/sulu/SuluCommunityBundle/issues/52)

**Merged pull requests:**

- Release 0.3.0 [\#59](https://github.com/sulu/SuluCommunityBundle/pull/59) ([alexander-schranz](https://github.com/alexander-schranz))
- Added missing default validation constraints [\#58](https://github.com/sulu/SuluCommunityBundle/pull/58) ([alexander-schranz](https://github.com/alexander-schranz))
- added documentation for last login refresh rate [\#56](https://github.com/sulu/SuluCommunityBundle/pull/56) ([alexander-schranz](https://github.com/alexander-schranz))
- add last login refresh request event listener [\#55](https://github.com/sulu/SuluCommunityBundle/pull/55) ([alexander-schranz](https://github.com/alexander-schranz))
- Also make name in emailadresses configurable [\#54](https://github.com/sulu/SuluCommunityBundle/pull/54) ([reyostallenberg](https://github.com/reyostallenberg))

## [0.2.0](https://github.com/sulu/SuluCommunityBundle/tree/0.2.0) (2017-03-06)
[Full Changelog](https://github.com/sulu/SuluCommunityBundle/compare/0.1.0...0.2.0)

**Fixed bugs:**

- Change profile avatar title from fullname to username [\#53](https://github.com/sulu/SuluCommunityBundle/pull/53) ([alexander-schranz](https://github.com/alexander-schranz))

## [0.1.0](https://github.com/sulu/SuluCommunityBundle/tree/0.1.0) (2017-02-21)
**Implemented enhancements:**

- Added builder to init community-bundle [\#4](https://github.com/sulu/SuluCommunityBundle/pull/4) ([wachterjohannes](https://github.com/wachterjohannes))

**Closed issues:**

- TODO List Release [\#34](https://github.com/sulu/SuluCommunityBundle/issues/34)
- Confirmation link language is ignored on redirect [\#32](https://github.com/sulu/SuluCommunityBundle/issues/32)
- Missing translations for blacklists [\#27](https://github.com/sulu/SuluCommunityBundle/issues/27)
- Functional testcases [\#10](https://github.com/sulu/SuluCommunityBundle/issues/10)
- Move Template Attributes to vendor sulu/sulu [\#3](https://github.com/sulu/SuluCommunityBundle/issues/3)

**Merged pull requests:**

- Replaced queries to find user [\#51](https://github.com/sulu/SuluCommunityBundle/pull/51) ([wachterjohannes](https://github.com/wachterjohannes))
- Prepared files for first release [\#49](https://github.com/sulu/SuluCommunityBundle/pull/49) ([wachterjohannes](https://github.com/wachterjohannes))
- Added functional testcases [\#48](https://github.com/sulu/SuluCommunityBundle/pull/48) ([wachterjohannes](https://github.com/wachterjohannes))
- Added testcase for email-confirmation if user has no email [\#44](https://github.com/sulu/SuluCommunityBundle/pull/44) ([wachterjohannes](https://github.com/wachterjohannes))
- Bugfix/email setting on registration [\#43](https://github.com/sulu/SuluCommunityBundle/pull/43) ([yanc3k](https://github.com/yanc3k))
- Fixed get correct encoder for new salt [\#39](https://github.com/sulu/SuluCommunityBundle/pull/39) ([alexander-schranz](https://github.com/alexander-schranz))
- Add documentation [\#36](https://github.com/sulu/SuluCommunityBundle/pull/36) ([alexander-schranz](https://github.com/alexander-schranz))
- Support sulu 1.3 version [\#35](https://github.com/sulu/SuluCommunityBundle/pull/35) ([alexander-schranz](https://github.com/alexander-schranz))
- allow route name and add locale replacer in confirmation redirect [\#33](https://github.com/sulu/SuluCommunityBundle/pull/33) ([alexander-schranz](https://github.com/alexander-schranz))
- Only check completion listener on safe http methods [\#31](https://github.com/sulu/SuluCommunityBundle/pull/31) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix validation for user subentity forms [\#30](https://github.com/sulu/SuluCommunityBundle/pull/30) ([alexander-schranz](https://github.com/alexander-schranz))
- Translate countries to correct language [\#29](https://github.com/sulu/SuluCommunityBundle/pull/29) ([alexander-schranz](https://github.com/alexander-schranz))
- remove contact when user is denied [\#26](https://github.com/sulu/SuluCommunityBundle/pull/26) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix registration block after deny over request [\#25](https://github.com/sulu/SuluCommunityBundle/pull/25) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix completion listener for not cache 302 redirect [\#24](https://github.com/sulu/SuluCommunityBundle/pull/24) ([alexander-schranz](https://github.com/alexander-schranz))
- Fixed delete items from list callback [\#23](https://github.com/sulu/SuluCommunityBundle/pull/23) ([chirimoya](https://github.com/chirimoya))
- Fix blacklist email [\#22](https://github.com/sulu/SuluCommunityBundle/pull/22) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix installation instructions [\#21](https://github.com/sulu/SuluCommunityBundle/pull/21) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix completion redirect for prod with fragments [\#20](https://github.com/sulu/SuluCommunityBundle/pull/20) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix password reset when user has salt [\#19](https://github.com/sulu/SuluCommunityBundle/pull/19) ([alexander-schranz](https://github.com/alexander-schranz))
- Set a block prefix for notes for easier theming [\#18](https://github.com/sulu/SuluCommunityBundle/pull/18) ([alexander-schranz](https://github.com/alexander-schranz))
- Fix installation instructions [\#16](https://github.com/sulu/SuluCommunityBundle/pull/16) ([alexander-schranz](https://github.com/alexander-schranz))
- Use sulu template attributes service [\#15](https://github.com/sulu/SuluCommunityBundle/pull/15) ([alexander-schranz](https://github.com/alexander-schranz))
- Rename twig file templates [\#14](https://github.com/sulu/SuluCommunityBundle/pull/14) ([alexander-schranz](https://github.com/alexander-schranz))
- Add block prefixes to simplify form theming [\#13](https://github.com/sulu/SuluCommunityBundle/pull/13) ([alexander-schranz](https://github.com/alexander-schranz))
- Implemented profile form and controller [\#12](https://github.com/sulu/SuluCommunityBundle/pull/12) ([wachterjohannes](https://github.com/wachterjohannes))
- Feature completion listener and form [\#11](https://github.com/sulu/SuluCommunityBundle/pull/11) ([alexander-schranz](https://github.com/alexander-schranz))
- Added blacklisting feature [\#9](https://github.com/sulu/SuluCommunityBundle/pull/9) ([wachterjohannes](https://github.com/wachterjohannes))
- Create user manager service [\#8](https://github.com/sulu/SuluCommunityBundle/pull/8) ([alexander-schranz](https://github.com/alexander-schranz))
- Add community manager service compiler pass [\#7](https://github.com/sulu/SuluCommunityBundle/pull/7) ([alexander-schranz](https://github.com/alexander-schranz))
- Bootstraped functional testcases [\#6](https://github.com/sulu/SuluCommunityBundle/pull/6) ([wachterjohannes](https://github.com/wachterjohannes))
- Init admin and javascript files [\#5](https://github.com/sulu/SuluCommunityBundle/pull/5) ([wachterjohannes](https://github.com/wachterjohannes))
- Add Registration, confirmation, password forget [\#2](https://github.com/sulu/SuluCommunityBundle/pull/2) ([alexander-schranz](https://github.com/alexander-schranz))



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*