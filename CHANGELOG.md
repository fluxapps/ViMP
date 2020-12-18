# Changelog

## [1.5.1]
- Fix: only show visible videos in repository preview

## [1.5.0]
- Fixed wrong date format in modal player
- Feature: configurable default access type (upload)
- Feature: configurable media permissions preselection (upload)
- Change: allow setting videos public for global admins

## [1.4.1]
- Fixed empty mediapermissions upload error

## [1.4.0]
- ILIAS 6 support
- Remove ILIAS 5.3 support
- Min. PHP 7.0
- Separate legacy cron job to `ViMPCron` wrapper plugin

## [1.3.9]
- Fix VIMP server 4.2.9 config need admin token
- Fix Docker-ILIAS log
- Fix Don't show hidden videos in repository object list preview
- Fix direct link to hidden videos access check

## [1.3.8]
- Get LP permission operations ids dynamic
- Revert wrong description line breaks try from v1.3.7
- Description line breaks in content tab (All 4 views) and add inline scroller if needed

## [1.3.7]
- Description line breaks

## [1.3.6]
- Bugfix: Introduced LP permissions

## [1.3.5]
- Bugfix: fixed direct link for content player (don't open modal)
- Bugfix: changed to mb_substr to avoid umlaut bugs
- Improvement: hide deleted videos in content tab

## [1.3.4]
- Bugfix: fixed bug when playing videos in page component plugin

## [1.3.3]
- Improvement: load LP observer only if LP is active
- Improvement: improved LP observer performance
- Change: changed intervall for LP observer from 2 to 5 sec

## [1.3.2]
- Bugfix: Player didn't work without adaptive streaming

## [1.3.1]
- Improvement: 360 degree video support
- Improvement: setting videos public/hidden can now be globally deactivated
- Bugfix: Check ViMP version for adaptive bitrate streaming
- Bugfix: Umlauts failed in shortened descriptions
- Bugfix: missing import in cronjob
- Bugfix: deep links didn't work for ilias 5.3

## [1.3.0]
- Feature: Deep Links for Videos
- Feature: show deep link in video and notification
- Feature: link thumbnail in repository preview to video

## [1.2.1]
- Bugfix: missing lang var for 'published' in own videos table
- Bugfix: video length >1h didn't show the hours
- Bugfix: HTML in video description showed tags

## [1.2.0]
- Feature: Support for ILIAS Version 5.4.x
- Change: Dropped support for ILIAS Version 5.2.x
- Feature: Config for CURL setting 'DISABLE_VERIFY_PEER'

## [1.1.0]
- Bugfix: Category Cache didn't work (should improve performance)
- Library: VideoJS included via npm now and upgraded to 7.5.5
- Feature: Added videojs-http-source-selector for adaptive streaming
- Improvement: Configs will be cached now (should improve performance)
- Improvement: Chapters will be cached now (should improve performance)
- Improvement: Version will be cached now (should improve performance)
- Improvement: changed button for showing own videos

## [1.0.5]
- Bugfix: PageComponent in ILIAS learning module not working correctly
- Bugfix: Possible Error when changing owner

## [1.0.4]
- Bugfix: videos deleted in vimp show msg "Transcoding"
- Bugfix when changing owner (mediapermission were lost)

## [1.0.3]
- Bugfix: after changing owner of a video, it couldn't be changed a 2nd time
- Bugfix: owner could not be changed if there's a required checkbox field

## [1.0.2]
- Usability Fix: Own Videos table shows button (-zone) "Show My Videos"

## [1.0.1]
- Bugfix: Youtube / Vimeo Videos could not be played in internal video player (embedded player is used now)
- Bugfix: check video status 'legal' in content tab
- Bugfix: user interface to change a video's owner now searches for ILIAS users instead of ViMP users
- Bugfix: plugin configuration field 'object title' could not be saved

## [1.0.0]
- First version
