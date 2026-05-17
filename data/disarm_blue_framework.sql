-- DISARM Blue Framework — MySQL Import
-- Connects to the existing Red Framework tables (phase, tactic, technique, task)
-- Blue hierarchy: metatechnique / responsetype → counter → tactic (RED) / technique (RED)
--                 detection → tactic (RED)
--                 actortype → counter (via junction)
--                 incident → technique (via junction)
--
-- INSTRUCTIONS:
--   1. Import disarm_red_framework.sql first (if not already done)
--   2. Open phpMyAdmin and select your database
--   3. Click "Import", upload this file, click "Go"
--   This script drops and recreates only the Blue Framework tables.
--   Red Framework tables are left intact.
--
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
-- TABLE: metatechnique
-- Blue-team strategy categories for counters
-- ============================================================
DROP TABLE IF EXISTS `metatechnique`;
CREATE TABLE `metatechnique` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_metatechnique_disarm_id` (`disarm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `metatechnique` (`id`, `disarm_id`, `name`, `summary`) VALUES
( 1, 'M001', 'Resilience',        'Increase the resilience to disinformation of the end subjects or other parts of the underlying system'),
( 2, 'M002', 'Diversion',         'Create alternative channels, messages etc in disinformation-prone systems'),
( 3, 'M003', 'Daylight',          'Make disinformation objects, mechanisms, messaging etc visible'),
( 4, 'M004', 'Friction',          'Slow down transmission or uptake of disinformation objects, messaging etc'),
( 5, 'M005', 'Removal',           'Remove disinformation objects from the system'),
( 6, 'M006', 'Scoring',           'Use a rating system'),
( 7, 'M007', 'Metatechnique',     ''),
( 8, 'M008', 'Data Pollution',    'Add artefacts to the underlying system that deliberately confound disinformation monitoring'),
( 9, 'M009', 'Dilution',          'Dilute disinformation artefacts and messaging with other content (kittens!)'),
(10, 'M010', 'Countermessaging',  'Create and distribute alternative messages to disinformation'),
(11, 'M011', 'Verification',      'Verify objects, content, connections etc. Includes fact-checking'),
(12, 'M012', 'Cleaning',          'Clean unneeded resources (accounts etc) from the underlying system so they can''t be used in disinformation'),
(13, 'M013', 'Targeting',         'Target the components of a disinformation campaign'),
(14, 'M014', 'Reduce Resources',  'Reduce the resources available to disinformation creators');

-- ============================================================
-- TABLE: responsetype
-- The D-codes: Detect / Deny / Disrupt / Degrade / Deceive / Destroy / Deter
-- ============================================================
DROP TABLE IF EXISTS `responsetype`;
CREATE TABLE `responsetype` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_responsetype_disarm_id` (`disarm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `responsetype` (`id`, `disarm_id`, `name`, `summary`) VALUES
(1, 'D01', 'Detect',  'Discover or discern the existence, presence, or fact of an intrusion into information systems.'),
(2, 'D02', 'Deny',    'Prevent disinformation creators from accessing and using critical information, systems, and services. Deny is for an indefinite time period.'),
(3, 'D03', 'Disrupt', 'Completely break or interrupt the flow of information, for a fixed amount of time. (Deny, for a limited time period). Not allowing any efficacy, for a short amount of time.'),
(4, 'D04', 'Degrade', 'Reduce the effectiveness or efficiency of disinformation creators'' command and control or communications systems, and information collection efforts or means, either indefinitely, or for a limited time period.'),
(5, 'D05', 'Deceive', 'Cause a person to believe what is not true. Military deception seeks to mislead adversary decision makers by manipulating their perception of reality.'),
(6, 'D06', 'Destroy', 'Damage a system or entity so badly that it cannot perform any function or be restored to a usable condition without being entirely rebuilt. Destroy is permanent.'),
(7, 'D07', 'Deter',   'Discourage.');

-- ============================================================
-- TABLE: actortype
-- Who implements counters (educators, platforms, governments…)
-- ============================================================
DROP TABLE IF EXISTS `actortype`;
CREATE TABLE `actortype` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  `sector_ids` VARCHAR(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_actortype_disarm_id` (`disarm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `actortype` (`id`, `disarm_id`, `name`, `summary`, `sector_ids`) VALUES
( 1, 'A001', 'data scientist',                    'Person who can wrangle data, implement machine learning algorithms etc',         'S001,S002,S003,S004,S005,S006,S007,S008,S009,S010'),
( 2, 'A002', 'target',                            'Person being targeted by disinformation campaign',                               'S001,S002,S003,S004,S005,S006,S007,S008,S009,S010'),
( 3, 'A003', 'trusted authority',                 'Influencer',                                                                    'S001,S002,S003,S004,S005,S006,S007,S008,S009,S010'),
( 4, 'A004', 'activist',                          '',                                                                              'S002'),
( 5, 'A005', 'community group',                   '',                                                                              'S002'),
( 6, 'A006', 'educator',                          '',                                                                              'S002'),
( 7, 'A007', 'factchecker',                       'Someone with the skills to verify whether information posted is factual',        'S002'),
( 8, 'A008', 'library',                           '',                                                                              'S002'),
( 9, 'A009', 'NGO',                               '',                                                                              'S002'),
(10, 'A010', 'religious organisation',            '',                                                                              'S002'),
(11, 'A011', 'school',                            '',                                                                              'S002'),
(12, 'A012', 'account owner',                     'Anyone who owns an account online',                                             'S006'),
(13, 'A013', 'content creator',                   '',                                                                              'S006'),
(14, 'A014', 'elves',                             '',                                                                              'S006'),
(15, 'A015', 'general public',                    '',                                                                              'S006'),
(16, 'A016', 'influencer',                        '',                                                                              'S006'),
(17, 'A017', 'coordinating body',                 'For example the DHS',                                                           'S003'),
(18, 'A018', 'government',                        'Government agencies',                                                           'S003'),
(19, 'A019', 'military',                          '',                                                                              'S003'),
(20, 'A020', 'policy maker',                      '',                                                                              'S003'),
(21, 'A021', 'media organisation',                '',                                                                              'S010'),
(22, 'A022', 'company',                           '',                                                                              'S009'),
(23, 'A023', 'adtech provider',                   '',                                                                              'S008'),
(24, 'A024', 'developer',                         '',                                                                              'S008'),
(25, 'A025', 'funding_site_admin',                'Funding site admin',                                                            'S008'),
(26, 'A026', 'games designer',                    '',                                                                              'S008'),
(27, 'A027', 'information security',              '',                                                                              'S008'),
(28, 'A028', 'platform administrator',            '',                                                                              'S008'),
(29, 'A029', 'server administrator',              '',                                                                              'S008'),
(30, 'A030', 'platforms',                         '',                                                                              'S007'),
(31, 'A031', 'social media platform administrator','Person with the authority to make changes to algorithms, take down content etc.','S007'),
(32, 'A032', 'social media platform outreach',    '',                                                                              'S007'),
(33, 'A033', 'social media platform owner',       'Person with authority to make changes to a social media company''s business model','S007');

-- ============================================================
-- TABLE: counter
-- Blue-team countermeasures; linked to tactic (RED), metatechnique, responsetype
-- tactic_id references tactic.disarm_id in the red framework
-- metatechnique_id references metatechnique.disarm_id
-- responsetype_id references responsetype.disarm_id
-- ============================================================
DROP TABLE IF EXISTS `counter`;
CREATE TABLE `counter` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  `metatechnique_id` VARCHAR(10) NOT NULL DEFAULT '',
  `tactic_id` VARCHAR(10) NOT NULL DEFAULT '',
  `responsetype_id` VARCHAR(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_counter_disarm_id` (`disarm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `counter` (`id`, `disarm_id`, `name`, `summary`, `metatechnique_id`, `tactic_id`, `responsetype_id`) VALUES
(  1, 'C00006', 'Charge for social media',                                        'Include a paid-for privacy option, e.g. pay Facebook for an option of them not collecting your personal information.',                                                                                                                             'M004', 'TA01', 'D02'),
(  2, 'C00008', 'Create shared fact-checking database',                           'Share fact-checking resources - tips, responses, countermessages, across response groups.',                                                                                                                                                        'M006', 'TA01', 'D04'),
(  3, 'C00009', 'Educate high profile influencers on best practices',             'Find online influencers. Provide training in the mechanisms of disinformation, how to spot campaigns, and/or how to contribute to responses by countermessaging, boosting information sites etc.',                                                  'M001', 'TA02', 'D02'),
(  4, 'C00010', 'Enhanced privacy regulation for social media',                   'Implement stronger privacy standards, to reduce the ability to microtarget community members.',                                                                                                                                                     'M004', 'TA01', 'D02'),
(  5, 'C00011', 'Media literacy. Games to identify fake news',                    'Create and use games to show people the mechanics of disinformation, and how to counter them.',                                                                                                                                                    'M001', 'TA02', 'D02'),
(  6, 'C00012', 'Platform regulation',                                            'Empower existing regulators to govern social media. Includes creating policy that makes social media police disinformation.',                                                                                                                       'M007', 'TA01', 'D02'),
(  7, 'C00013', 'Rating framework for news',                                      'Strategic inoculation, raising the standards of what people expect in terms of evidence when consuming news.',                                                                                                                                      'M006', 'TA01', 'D02'),
(  8, 'C00014', 'Real-time updates to fact-checking database',                    'Update fact-checking databases and resources in real time. Especially important for time-limited events like natural disasters.',                                                                                                                    'M006', 'TA06', 'D04'),
(  9, 'C00016', 'Censorship',                                                     'Alter and/or block the publication/dissemination of information controlled by disinformation creators. Not recommended.',                                                                                                                           'M005', 'TA01', 'D02'),
( 10, 'C00017', 'Repair broken social connections',                               'Use a media campaign to promote in-group to out-group in person communication / activities.',                                                                                                                                                      'M010', 'TA01', 'D03'),
( 11, 'C00019', 'Reduce effect of division-enablers',                             'Includes promoting constructive communication by shaming division-enablers.',                                                                                                                                                                       'M003', 'TA01', 'D03'),
( 12, 'C00021', 'Encourage in-person communication',                              'Encourage offline communication.',                                                                                                                                                                                                                  'M001', 'TA01', 'D04'),
( 13, 'C00022', 'Innoculate. Positive campaign to promote feeling of safety',     'Used to counter ability based and fear based attacks.',                                                                                                                                                                                              'M001', 'TA01', 'D04'),
( 14, 'C00024', 'Promote healthy narratives',                                     'Includes promoting constructive narratives i.e. not polarising. Includes promoting identity neutral narratives.',                                                                                                                                   'M001', 'TA01', 'D04'),
( 15, 'C00026', 'Shore up democracy based messages',                              'Messages about peace, freedom. Implement a compelling narrative via effective mechanisms of communication.',                                                                                                                                         'M010', 'TA01', 'D04'),
( 16, 'C00027', 'Create culture of civility',                                     'This is passive. Includes promoting civility as an identity that people will defend.',                                                                                                                                                              'M001', 'TA01', 'D07'),
( 17, 'C00028', 'Make information provenance available',                          'Blockchain audit log and validation with collaborative decryption to post comments.',                                                                                                                                                               'M011', 'TA02', 'D03'),
( 18, 'C00029', 'Create fake website to issue counter narrative',                 'Create websites in disinformation voids - spaces where people are looking for known disinformation.',                                                                                                                                               'M002', 'TA02', 'D03'),
( 19, 'C00030', 'Develop a compelling counter narrative (truth based)',           '',                                                                                                                                                                                                                                                  'M002', 'TA02', 'D03'),
( 20, 'C00031', 'Dilute the core narrative - create multiple permutations',       'Create competing narratives.',                                                                                                                                                                                                                      'M009', 'TA02', 'D03'),
( 21, 'C00032', 'Hijack content and link to truth-based info',                    'Link to platform.',                                                                                                                                                                                                                                 'M002', 'TA06', 'D03'),
( 22, 'C00034', 'Create more friction at account creation',                       'Counters fake account.',                                                                                                                                                                                                                             'M004', 'TA15', 'D04'),
( 23, 'C00036', 'Infiltrate the in-group to discredit leaders (divide)',          'All of these would be highly affected by infiltration or false-claims of infiltration.',                                                                                                                                                            'M013', 'TA15', 'D02'),
( 24, 'C00040', 'Third party verification for people',                            'Counters fake experts.',                                                                                                                                                                                                                             'M011', 'TA15', 'D02'),
( 25, 'C00042', 'Address truth contained in narratives',                          'Focus on and boost truths in misinformation narratives, removing misinformation from them.',                                                                                                                                                        'M010', 'TA15', 'D04'),
( 26, 'C00044', 'Keep people from posting to social media immediately',           'Platforms can introduce friction to slow down activities, force a small delay between posts, or replies to posts.',                                                                                                                                  'M004', 'TA15', 'D03'),
( 27, 'C00046', 'Marginalise and discredit extremist groups',                     'Reduce the credibility of extremist groups posting misinformation.',                                                                                                                                                                                'M013', 'TA15', 'D04'),
( 28, 'C00047', 'Honeypot with coordinated inauthentics',                         'Flood disinformation spaces with obviously fake content, to dilute core misinformation narratives in them.',                                                                                                                                        'M008', 'TA15', 'D05'),
( 29, 'C00048', 'Name and Shame Influencers',                                     'Identify social media accounts as sources of propaganda - calling them out.',                                                                                                                                                                       'M003', 'TA15', 'D07'),
( 30, 'C00051', 'Counter social engineering training',                            'Includes anti-elicitation training, phishing prevention education.',                                                                                                                                                                                'M001', 'TA15', 'D02'),
( 31, 'C00052', 'Infiltrate platforms',                                           'Detect and degrade.',                                                                                                                                                                                                                                'M013', 'TA15', 'D04'),
( 32, 'C00053', 'Delete old accounts / Remove unused social media accounts',      'Remove or remove access to old social media accounts, to reduce the pool of accounts available for takeover, botnets etc.',                                                                                                                        'M012', 'TA15', 'D04'),
( 33, 'C00056', 'Encourage people to leave social media',                         'Encourage people to leave social media.',                                                                                                                                                                                                            'M004', 'TA15', 'D02'),
( 34, 'C00058', 'Report crowdfunder as violator',                                 'Counters crowdfunding. Includes Expose online funding as fake.',                                                                                                                                                                                    'M005', 'TA15', 'D02'),
( 35, 'C00059', 'Verification of project before posting fund requests',           'Third-party verification of projects posting funding campaigns before those campaigns can be posted.',                                                                                                                                              'M011', 'TA15', 'D02'),
( 36, 'C00060', 'Legal action against for-profit engagement factories',           'Take legal action against for-profit factories creating misinformation.',                                                                                                                                                                           'M013', 'TA02', 'D03'),
( 37, 'C00062', 'Free open library sources worldwide',                            'Open-source libraries could be created that aid in some way for each technique.',                                                                                                                                                                   'M010', 'TA15', 'D04'),
( 38, 'C00065', 'Reduce political targeting',                                     'Includes ban political micro targeting and ban political ads.',                                                                                                                                                                                      'M005', 'TA05', 'D03'),
( 39, 'C00066', 'Co-opt a hashtag and drown it out (hijack it back)',             'Flood a disinformation-related hashtag with other content.',                                                                                                                                                                                        'M009', 'TA05', 'D03'),
( 40, 'C00067', 'Denigrate the recipient/project of online funding',              'Reduce the credibility of groups behind misinformation-linked funding campaigns.',                                                                                                                                                                  'M013', 'TA15', 'D03'),
( 41, 'C00070', 'Block access to disinformation resources',                       'Resources = accounts, channels etc. Block access to platform.',                                                                                                                                                                                     'M005', 'TA02', 'D02'),
( 42, 'C00071', 'Block source of pollution',                                      'Block websites, accounts, groups etc connected to misinformation and other information pollution.',                                                                                                                                                 'M005', 'TA06', 'D02'),
( 43, 'C00072', 'Remove non-relevant content from special interest groups',       'Check special-interest groups for unrelated and misinformation-linked content, and remove it. Not recommended.',                                                                                                                                    'M005', 'TA06', 'D02'),
( 44, 'C00073', 'Inoculate populations through media literacy training',          'Use training to build the resilience of at-risk populations. Educate on how to handle info pollution.',                                                                                                                                             'M001', 'TA01', 'D02'),
( 45, 'C00074', 'Identify and delete or rate limit identical content',            '',                                                                                                                                                                                                                                                  'M012', 'TA06', 'D02'),
( 46, 'C00075', 'Normalise language',                                             'Normalise the language around disinformation and misinformation; give people the words for artefact and effect types.',                                                                                                                             'M010', 'TA06', 'D02'),
( 47, 'C00076', 'Prohibit images in political discourse channels',                'Make political discussion channels text-only.',                                                                                                                                                                                                      'M005', 'TA06', 'D02'),
( 48, 'C00077', 'Active defence: run TA15 develop people - not recommended',      'Develop networks of communities and influencers around counter-misinformation.',                                                                                                                                                                    'M013', 'TA15', 'D03'),
( 49, 'C00078', 'Change Search Algorithms for Disinformation Content',           'Includes change image search algorithms for hate groups and extremists.',                                                                                                                                                                            'M002', 'TA06', 'D03'),
( 50, 'C00080', 'Create competing narrative',                                     'Create counternarratives, or narratives that compete in the same spaces as misinformation narratives.',                                                                                                                                             'M002', 'TA06', 'D03'),
( 51, 'C00081', 'Highlight flooding and noise, and explain motivations',          'Discredit by pointing out the noise and informing public that flooding is a technique of disinformation campaigns.',                                                                                                                                'M003', 'TA06', 'D03'),
( 52, 'C00082', 'Ground truthing as automated response to pollution',             'Also inoculation.',                                                                                                                                                                                                                                  'M010', 'TA06', 'D03'),
( 53, 'C00084', 'Modify disinformation narratives, and rebroadcast them',         'Includes poison pill recasting of message and steal their truths.',                                                                                                                                                                                 'M002', 'TA06', 'D03'),
( 54, 'C00085', 'Mute content',                                                   'Rate-limit disinformation content. Reduces its effects, whilst not running afoul of censorship concerns.',                                                                                                                                          'M003', 'TA06', 'D03'),
( 55, 'C00086', 'Distract from noise with addictive content',                     'Interject addictive links or contents into discussions of disinformation materials.',                                                                                                                                                               'M002', 'TA06', 'D04'),
( 56, 'C00087', 'Make more noise than the disinformation',                        '',                                                                                                                                                                                                                                                  'M009', 'TA06', 'D04'),
( 57, 'C00090', 'Fake engagement system',                                         'Create honeypots for misinformation creators to engage with, and reduce the resources they have available.',                                                                                                                                        'M002', 'TA07', 'D05'),
( 58, 'C00091', 'Honeypot social community',                                      'Set honeypots, e.g. communities, in networks likely to be used for disinformation.',                                                                                                                                                               'M002', 'TA06', 'D05'),
( 59, 'C00092', 'Establish a truth teller reputation score for influencers',      'Includes Reputation scores for social media users.',                                                                                                                                                                                                'M006', 'TA02', 'D07'),
( 60, 'C00093', 'Influencer code of conduct',                                     'Establish tailored code of conduct for individuals with many followers.',                                                                                                                                                                           'M001', 'TA15', 'D07'),
( 61, 'C00094', 'Force full disclosure on corporate sponsor of research',         'Accountability move: make sure research is published with its funding sources.',                                                                                                                                                                    'M003', 'TA06', 'D04'),
( 62, 'C00096', 'Strengthen institutions that are always truth tellers',          'Increase credibility, visibility, and reach of positive influencers in the information space.',                                                                                                                                                     'M006', 'TA01', 'D07'),
( 63, 'C00097', 'Require use of verified identities to contribute to poll',       'Reduce poll flooding by only taking comments or poll entries from verified accounts.',                                                                                                                                                              'M004', 'TA07', 'D02'),
( 64, 'C00098', 'Revocation of allowlisted or verified status',                   'Remove blue checkmarks etc from known misinformation accounts.',                                                                                                                                                                                    'M004', 'TA07', 'D02'),
( 65, 'C00099', 'Strengthen verification methods',                                'Improve content verification methods available to groups, individuals etc.',                                                                                                                                                                        'M004', 'TA07', 'D02'),
( 66, 'C00100', 'Hashtag jacking',                                                'Post large volumes of unrelated content on known misinformation hashtags.',                                                                                                                                                                         'M002', 'TA08', 'D03'),
( 67, 'C00101', 'Create friction by rate-limiting engagement',                    'Create participant friction. Includes Make repeat voting hard, and throttle number of forwards.',                                                                                                                                                   'M004', 'TA07', 'D04'),
( 68, 'C00103', 'Create a bot that engages / distract trolls',                    'This is reactive, not active measure (honeypots are active). It is a platform controlled measure.',                                                                                                                                                'M002', 'TA07', 'D05'),
( 69, 'C00105', 'Buy more advertising than misinformation creators',              'Shift influence and algorithms by posting more adverts into spaces than misinformation creators.',                                                                                                                                                  'M009', 'TA07', 'D03'),
( 70, 'C00106', 'Click-bait centrist content',                                    'Create emotive centrist content that gets more clicks.',                                                                                                                                                                                             'M002', 'TA06', 'D03'),
( 71, 'C00107', 'Content moderation',                                             'Includes social media content take-downs, e.g. Facebook or Twitter content take-downs.',                                                                                                                                                            'M006', 'TA06', 'D02'),
( 72, 'C00109', 'Dampen Emotional Reaction',                                      'Reduce emotional responses to misinformation through calming messages, etc.',                                                                                                                                                                       'M001', 'TA09', 'D03'),
( 73, 'C00111', 'Reduce polarisation by connecting and presenting sympathetic renditions of opposite views', '',                                                                                                                                                                                                                       'M001', 'TA01', 'D04'),
( 74, 'C00112', '"Prove they are not an op!"',                                    'Challenge misinformation creators to prove they''re not an information operation.',                                                                                                                                                                 'M004', 'TA08', 'D02'),
( 75, 'C00113', 'Debunk and defuse a fake expert / credentials',                  'Debunk fake experts, their credentials, and potentially also their audience quality.',                                                                                                                                                              'M003', 'TA08', 'D02'),
( 76, 'C00114', 'Don''t engage with payloads',                                   'Stop passing on misinformation.',                                                                                                                                                                                                                    'M004', 'TA08', 'D02'),
( 77, 'C00115', 'Expose actor and intentions',                                    'Debunk misinformation creators and posters.',                                                                                                                                                                                                        'M003', 'TA08', 'D02'),
( 78, 'C00116', 'Provide proof of involvement',                                   'Build and post information about groups etc''s involvement in misinformation incidents.',                                                                                                                                                           'M003', 'TA08', 'D02'),
( 79, 'C00117', 'Downgrade / de-amplify so message is seen by fewer people',     'Label promote counter to disinformation.',                                                                                                                                                                                                           'M010', 'TA08', 'D04'),
( 80, 'C00118', 'Repurpose images with new text',                                 'Add countermessage text to images used in misinformation incidents.',                                                                                                                                                                               'M010', 'TA08', 'D04'),
( 81, 'C00119', 'Engage payload and debunk',                                      'Debunk misinformation content. Provide link to facts.',                                                                                                                                                                                              'M010', 'TA08', 'D07'),
( 82, 'C00120', 'Open dialogue about design of platforms to produce different outcomes', 'Redesign platforms and algorithms to reduce the effectiveness of disinformation.',                                                                                                                                                           'M007', 'TA08', 'D07'),
( 83, 'C00121', 'Tool transparency and literacy for channels people follow',      'Make algorithms in platforms explainable, and visible to people using those platforms.',                                                                                                                                                            'M001', 'TA08', 'D07'),
( 84, 'C00122', 'Content moderation',                                             'Beware: content moderation misused becomes censorship.',                                                                                                                                                                                             'M004', 'TA09', 'D02'),
( 85, 'C00123', 'Remove or rate limit botnets',                                   'Reduce the visibility of known botnets online.',                                                                                                                                                                                                    'M004', 'TA09', 'D03'),
( 86, 'C00124', 'Don''t feed the trolls',                                        'Don''t engage with individuals relaying misinformation.',                                                                                                                                                                                            'M004', 'TA09', 'D03'),
( 87, 'C00125', 'Prebunking',                                                     'Produce material in advance of misinformation incidents, by anticipating the narratives used in them, and debunking them.',                                                                                                                         'M001', 'TA09', 'D03'),
( 88, 'C00126', 'Social media amber alert',                                       'Create an alert system around disinformation and misinformation artefacts, narratives, and incidents.',                                                                                                                                             'M003', 'TA09', 'D03'),
( 89, 'C00128', 'Create friction by marking content with ridicule or other decelerants', 'Repost or comment on misinformation artefacts, using ridicule or other content to reduce the likelihood of reposting.',                                                                                                                     'M009', 'TA09', 'D03'),
( 90, 'C00129', 'Use banking to cut off access',                                  'Fiscal sanctions; parallel to counter terrorism.',                                                                                                                                                                                                  'M014', 'TA09', 'D02'),
( 91, 'C00130', 'Mentorship: elders, youth, credit. Learn vicariously.',          'Train local influencers in countering misinformation.',                                                                                                                                                                                             'M001', 'TA05', 'D07'),
( 92, 'C00131', 'Seize and analyse botnet servers',                               'Take botnet servers offline by seizing them.',                                                                                                                                                                                                      'M005', 'TA11', 'D02'),
( 93, 'C00133', 'Deplatform Account',                                             'Similar to Deplatform People but less generic.',                                                                                                                                                                                                    'M005', 'TA15', 'D03'),
( 94, 'C00135', 'Deplatform message groups and/or message boards',                'Merged two rows here.',                                                                                                                                                                                                                             'M005', 'TA15', 'D03'),
( 95, 'C00136', 'Microtarget most likely targets then send them countermessages', 'Find communities likely to be targeted by misinformation campaigns, and send them countermessages.',                                                                                                                                                'M010', 'TA08', 'D03'),
( 96, 'C00138', 'Spam domestic actors with lawsuits',                             'File multiple lawsuits against known misinformation creators and posters, to distract them from disinformation creation.',                                                                                                                          'M014', 'TA11', 'D03'),
( 97, 'C00139', 'Weaponise youtube content matrices',                             'God knows what this is. Keeping temporarily in case we work it out.',                                                                                                                                                                              'M004', 'TA11', 'D03'),
( 98, 'C00140', '"Bomb" link shorteners with lots of calls',                      'Applies to most of the content used by exposure techniques.',                                                                                                                                                                                       'M008', 'TA12', 'D03'),
( 99, 'C00142', 'Platform adds warning label and decision point when sharing content', 'Includes this has been disproved: do you want to forward it.',                                                                                                                                                                                'M004', 'TA06', 'D04'),
(100, 'C00143', '(botnet) DMCA takedown requests to waste group time',            'Use copyright infringement claims to remove videos etc.',                                                                                                                                                                                           'M013', 'TA11', 'D04'),
(101, 'C00144', 'Buy out troll farm employees / offer them jobs',                 'Degrade the infrastructure. Could e.g. pay to not act for 30 days. Not recommended.',                                                                                                                                                              'M014', 'TA02', 'D04'),
(102, 'C00147', 'Make amplification of social media posts expire',                'Stop new community activity (likes, comments) on old social media posts.',                                                                                                                                                                          'M004', 'TA09', 'D03'),
(103, 'C00148', 'Add random links to network graphs',                             'If creators are using network analysis to determine how to attack networks, then adding random extra links might throw that analysis out.',                                                                                                          'M008', 'TA12', 'D04'),
(104, 'C00149', 'Poison the monitoring & evaluation data',                        'Includes Pollute the AB-testing data feeds.',                                                                                                                                                                                                       'M008', 'TA12', 'D04'),
(105, 'C00153', 'Take pre-emptive action against actors infrastructure',          'Align offensive cyber action with information operations and counter disinformation approaches, where appropriate.',                                                                                                                                 'M013', 'TA01', 'D03'),
(106, 'C00154', 'Ask media not to report false information',                      'Train media to spot and respond to misinformation, and ask them not to post or transmit misinformation they''ve found.',                                                                                                                            'M005', 'TA08', 'D02'),
(107, 'C00155', 'Ban incident actors from funding sites',                         'Ban misinformation creators and posters from funding sites.',                                                                                                                                                                                        'M005', 'TA15', 'D02'),
(108, 'C00156', 'Better tell your country or organisation story',                 'Civil engagement activities conducted on the part of EFP forces.',                                                                                                                                                                                  'M010', 'TA02', 'D03'),
(109, 'C00159', 'Have a disinformation response plan',                            'Create a campaign plan and toolkit for competition short of armed conflict.',                                                                                                                                                                        'M007', 'TA01', 'D03'),
(110, 'C00160', 'Find and train influencers',                                     'Identify key influencers (e.g. use network analysis), then reach out to identified users and offer support.',                                                                                                                                       'M001', 'TA15', 'D02'),
(111, 'C00161', 'Coalition Building with stakeholders and Third-Party Inducements','Advance coalitions across borders and sectors, spanning public and private divides.',                                                                                                                                                              'M007', 'TA01', 'D07'),
(112, 'C00162', 'Unravel/target the Potemkin villages',                           'Kremlin''s narrative spin extends through constellations of civil society organisations, political parties, churches, and other actors.',                                                                                                           'M013', 'TA15', 'D03'),
(113, 'C00164', 'Compatriot policy',                                              'Protect the interests of diaspora populations and, more importantly, influence the population to support pro-state causes.',                                                                                                                         'M013', 'TA02', 'D03'),
(114, 'C00165', 'Ensure integrity of official documents',                         'E.g. for leaked legal documents, use court motions to limit future discovery actions.',                                                                                                                                                             'M004', 'TA06', 'D02'),
(115, 'C00169', 'Develop a creative content hub',                                 'International donors will donate to a basket fund that will pay a committee of local experts to manage and distribute money to Russian-language producers.',                                                                                         'M010', 'TA02', 'D03'),
(116, 'C00170', 'Elevate information as a critical domain of statecraft',         'Shift from reactive to proactive response, with priority on sharing relevant information with the public.',                                                                                                                                         'M007', 'TA01', 'D03'),
(117, 'C00172', 'Social media source removal',                                    'Removing accounts, pages, groups, e.g. Facebook page removal.',                                                                                                                                                                                    'M005', 'TA15', 'D02'),
(118, 'C00174', 'Create a healthier news environment',                            'Free and fair press: create bipartisan, patriotic commitment to press freedom. Build alternative news sources.',                                                                                                                                     'M007', 'TA01', 'D02'),
(119, 'C00176', 'Improve Coordination amongst stakeholders: public and private',  'Coordinated disinformation challenges are increasingly multidisciplinary.',                                                                                                                                                                         'M007', 'TA01', 'D07'),
(120, 'C00178', 'Fill information voids with non-disinformation content',         '1) Pollute the data voids with wholesome content. 2) Fill data voids with relevant information.',                                                                                                                                                   'M009', 'TA05', 'D04'),
(121, 'C00182', 'Redirection / malware detection / remediation',                  'Detect redirection or malware, then quarantine or delete.',                                                                                                                                                                                         'M005', 'TA09', 'D02'),
(122, 'C00184', 'Media exposure',                                                 'Highlight misinformation activities and actors in media.',                                                                                                                                                                                           'M003', 'TA08', 'D04'),
(123, 'C00188', 'Newsroom/Journalist training to counter influence moves',        'Includes SEO influence. Includes promotion of a higher standard of journalism.',                                                                                                                                                                    'M001', 'TA08', 'D03'),
(124, 'C00189', 'Ensure that platforms are taking down flagged accounts',         'Use ongoing analysis/monitoring of flagged profiles.',                                                                                                                                                                                               'M003', 'TA15', 'D06'),
(125, 'C00190', 'Open engagement with civil society',                             'Government open engagement with civil society as an independent check on government action and messaging.',                                                                                                                                          'M001', 'TA01', 'D03'),
(126, 'C00195', 'Redirect searches away from disinformation or extremist content','Use Google AdWords to identify instances in which people search about particular fake-news stories.',                                                                                                                                               'M002', 'TA07', 'D02'),
(127, 'C00197', 'Remove suspicious accounts',                                     'Standard reporting for false profiles (identity issues). Includes detecting hijacked accounts.',                                                                                                                                                    'M005', 'TA15', 'D02'),
(128, 'C00200', 'Respected figure (influencer) disavows misinfo',                 'FIXIT: standardise language used for influencer/respected figure.',                                                                                                                                                                                 'M010', 'TA09', 'D03'),
(129, 'C00202', 'Set data honeytraps',                                            'Set honeytraps in content likely to be accessed for disinformation.',                                                                                                                                                                               'M002', 'TA06', 'D02'),
(130, 'C00203', 'Stop offering press credentials to propaganda outlets',          'Remove access to official press events from known misinformation actors.',                                                                                                                                                                          'M004', 'TA15', 'D03'),
(131, 'C00205', 'Strong dialogue between the federal government and private sector','Increase civic resilience by partnering with business community to combat grey zone threats.',                                                                                                                                                    'M007', 'TA01', 'D03'),
(132, 'C00207', 'Run a competing disinformation campaign - not recommended',      '',                                                                                                                                                                                                                                                  'M013', 'TA02', 'D07'),
(133, 'C00211', 'Use humorous counter-narratives',                                '',                                                                                                                                                                                                                                                  'M010', 'TA09', 'D03'),
(134, 'C00212', 'Build public resilience by making civil society more vibrant',   'Increase public service experience, and support wider civics and history education.',                                                                                                                                                               'M001', 'TA01', 'D03'),
(135, 'C00216', 'Use advertiser controls to stem flow of funds to bad actors',    'Prevent ad revenue going to disinformation domains.',                                                                                                                                                                                               'M014', 'TA05', 'D02'),
(136, 'C00219', 'Add metadata to content out of control of disinformation creators','Steganography. Adding date, signatures etc to stop issue of photo relabelling.',                                                                                                                                                                 'M003', 'TA06', 'D04'),
(137, 'C00220', 'Develop a monitoring and intelligence plan',                     'Create a plan for misinformation and disinformation response, before it is needed.',                                                                                                                                                                'M007', 'TA01', 'D03'),
(138, 'C00221', 'Run a disinformation red team, and design mitigation factors',   'Include PACE plans - Primary, Alternate, Contingency, Emergency.',                                                                                                                                                                                  'M007', 'TA01', 'D03'),
(139, 'C00222', 'Tabletop simulations',                                           'Simulate misinformation and disinformation campaigns, and responses to them, before campaigns happen.',                                                                                                                                             'M007', 'TA02', 'D03'),
(140, 'C00223', 'Strengthen Trust in social media platforms',                     'Improve trust in the misinformation responses from social media and other platforms.',                                                                                                                                                              'M001', 'TA01', 'D03');

-- ============================================================
-- TABLE: counter_technique
-- Many-to-many: which red techniques does each counter address?
-- counter_id references counter.disarm_id
-- technique_id references technique.disarm_id
-- ============================================================
DROP TABLE IF EXISTS `counter_technique`;
CREATE TABLE `counter_technique` (
  `counter_id` VARCHAR(10) NOT NULL,
  `technique_id` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`counter_id`, `technique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `counter_technique` (`counter_id`, `technique_id`) VALUES
('C00009', 'T0010'),
('C00029', 'T0002'),
('C00030', 'T0002'),
('C00031', 'T0002'),
('C00042', 'T0004'),
('C00044', 'T0029'),
('C00046', 'T0010'),
('C00048', 'T0010'),
('C00051', 'T0010'),
('C00058', 'T0017'),
('C00059', 'T0014'),
('C00065', 'T0018'),
('C00066', 'T0015'),
('C00067', 'T0017'),
('C00073', 'T0016'),
('C00076', 'T0016'),
('C00080', 'T0003'),
('C00081', 'T0003'),
('C00082', 'T0002'),
('C00084', 'T0002'),
('C00086', 'T0044'),
('C00087', 'T0039'),
('C00090', 'T0020'),
('C00096', 'T0022'),
('C00097', 'T0029'),
('C00101', 'T0029'),
('C00103', 'T0029'),
('C00105', 'T0016'),
('C00106', 'T0016'),
('C00111', 'T0010'),
('C00112', 'T0040'),
('C00113', 'T0045'),
('C00114', 'T0039'),
('C00117', 'T0046'),
('C00118', 'T0044'),
('C00119', 'T0022'),
('C00120', 'T0047'),
('C00123', 'T0029'),
('C00129', 'T0057'),
('C00130', 'T0010'),
('C00131', 'T0049'),
('C00138', 'T0060'),
('C00143', 'T0060'),
('C00147', 'T0060'),
('C00154', 'T0039'),
('C00155', 'T0014'),
('C00156', 'T0022'),
('C00160', 'T0039'),
('C00161', 'T0022'),
('C00162', 'T0010'),
('C00164', 'T0022'),
('C00169', 'T0010'),
('C00178', 'T0016'),
('C00184', 'T0045'),
('C00195', 'T0010'),
('C00200', 'T0010'),
('C00203', 'T0010'),
('C00216', 'T0014');

-- ============================================================
-- TABLE: detection
-- Blue-team detections; linked to tactic (RED) and responsetype
-- Data sourced from detections_index.md (no individual detail files exist)
-- ============================================================
DROP TABLE IF EXISTS `detection`;
CREATE TABLE `detection` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  `metatechnique_id` VARCHAR(10) NOT NULL DEFAULT '',
  `tactic_id` VARCHAR(10) NOT NULL DEFAULT '',
  `responsetype_id` VARCHAR(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_detection_disarm_id` (`disarm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `detection` (`id`, `disarm_id`, `name`, `summary`, `metatechnique_id`, `tactic_id`, `responsetype_id`) VALUES
( 1, 'F00001', 'Analyse aborted / failed campaigns',                       'Examine failed campaigns. How did they fail? Can we create useful activities that increase these failures?',                                                                    '',     'TA01', 'D01'),
( 2, 'F00002', 'Analyse viral fizzle',                                      'Examine the way a viral story spreads and how it fizzles.',                                                                                                                    '',     'TA01', 'D01'),
( 3, 'F00003', 'Exploit counter-intelligence vs bad actors',                '',                                                                                                                                                                             '',     'TA01', 'D01'),
( 4, 'F00004', 'Recruit like-minded converts',                              '',                                                                                                                                                                             '',     'TA01', 'D01'),
( 5, 'F00005', 'SWOT Analysis of Cognition in Various Groups',              'Strengths, Weaknesses, Opportunities, Threats analysis of groups and audience segments.',                                                                                      '',     'TA01', 'D01'),
( 6, 'F00006', 'SWOT analysis of tech platforms',                          '',                                                                                                                                                                             '',     'TA01', 'D01'),
( 7, 'F00007', 'Monitor account level activity in social networks',         '',                                                                                                                                                                             '',     'TA02', 'D01'),
( 8, 'F00008', 'Detect abnormal amplification',                             '',                                                                                                                                                                             '',     'TA15', 'D01'),
( 9, 'F00009', 'Detect abnormal events',                                    '',                                                                                                                                                                             '',     'TA15', 'D01'),
(10, 'F00010', 'Detect abnormal groups',                                    '',                                                                                                                                                                             '',     'TA15', 'D01'),
(11, 'F00011', 'Detect abnormal pages',                                     '',                                                                                                                                                                             '',     'TA15', 'D01'),
(12, 'F00012', 'Detect abnormal profiles, e.g. prolific pages/groups/people','',                                                                                                                                                                            '',     'TA15', 'D01'),
(13, 'F00013', 'Identify fake news sites',                                  '',                                                                                                                                                                             '',     'TA15', 'D01'),
(14, 'F00014', 'Trace connections',                                         'For example, for fake news sites.',                                                                                                                                            '',     'TA15', 'D01'),
(15, 'F00015', 'Detect anomalies in membership growth patterns',            'I include Fake Experts as they may use funding campaigns such as Patreon to fund their operations.',                                                                           '',     'TA15', 'D01'),
(16, 'F00016', 'Identify fence-sitters',                                    'Depending on the platform there may be a way to identify a fence-sitter.',                                                                                                    '',     'TA15', 'D01'),
(17, 'F00017', 'Measure emotional valence',                                 '',                                                                                                                                                                             '',     'TA15', 'D01'),
(18, 'F00018', 'Follow the money',                                          'Track funding sources.',                                                                                                                                                       '',     'TA15', 'D01'),
(19, 'F00019', 'Activity resurgence detection',                             'Alarm when dormant accounts become activated.',                                                                                                                                '',     'TA15', 'D01'),
(20, 'F00020', 'Detect anomalous activity',                                 '',                                                                                                                                                                             '',     'TA15', 'D01'),
(21, 'F00021', 'AI/ML automated early detection of campaign planning',      '',                                                                                                                                                                             '',     'TA15', 'D01'),
(22, 'F00022', 'Digital authority - regulating body',                       '',                                                                                                                                                                             '',     'TA15', 'D01'),
(23, 'F00023', 'Periodic verification (counter to hijack legitimate account)','',                                                                                                                                                                           '',     'TA15', 'D01'),
(24, 'F00024', 'Teach civics to kids / adults / seniors',                   '',                                                                                                                                                                             '',     'TA15', 'D01'),
(25, 'F00025', 'Boots-on-the-ground early narrative detection',             '',                                                                                                                                                                             '',     'TA05', 'D01'),
(26, 'F00026', 'Language anomaly detection',                                '',                                                                                                                                                                             '',     'TA05', 'D01'),
(27, 'F00027', 'Unlikely correlation of sentiment on same topics',          '',                                                                                                                                                                             '',     'TA05', 'D01'),
(28, 'F00028', 'Associate a public key signature with government documents', '',                                                                                                                                                                            '',     'TA06', 'D01'),
(29, 'F00029', 'Detect proto narratives, i.e. RT, Sputnik',                '',                                                                                                                                                                             '',     'TA06', 'D01'),
(30, 'F00030', 'Early detection and warning - reporting of suspect content','',                                                                                                                                                                             '',     'TA06', 'D01'),
(31, 'F00031', 'Educate on how to identify information pollution',          'Strategic planning included as inoculating population has strategic value.',                                                                                                    '',     'TA06', 'D01'),
(32, 'F00033', 'Fake websites: add transparency on business model',         '',                                                                                                                                                                             '',     'TA06', 'D01'),
(33, 'F00034', 'Flag the information spaces so people know about active flooding effort','',                                                                                                                                                                '',     'TA06', 'D01'),
(34, 'F00035', 'Identify repeated narrative DNA',                           '',                                                                                                                                                                             '',     'TA06', 'D01'),
(35, 'F00036', 'Looking for AB testing in unregulated channels',            '',                                                                                                                                                                             '',     'TA06', 'D01'),
(36, 'F00037', 'News content provenance certification',                     'Raising the standards of what people expect in terms of evidence when consuming news.',                                                                                        '',     'TA06', 'D01'),
(37, 'F00038', 'Social capital as attack vector',                           'Track ignorant agents who fall into the enemy trap.',                                                                                                                          '',     'TA06', 'D01'),
(38, 'F00039', 'Standards to track image / video deep fakes - industry',    '',                                                                                                                                                                             '',     'TA06', 'D01'),
(39, 'F00040', 'Unalterable metadata signature on origins of image and provenance','',                                                                                                                                                                      '',     'TA06', 'D01'),
(40, 'F00041', 'Bias detection',                                            'Not technically left of boom.',                                                                                                                                               '',     'TA07', 'D01'),
(41, 'F00042', 'Categorise polls by intent',                                'Use T0029 against the creators.',                                                                                                                                              '',     'TA07', 'D01'),
(42, 'F00043', 'Monitor for creation of fake known personas',               'Platform companies and some information security companies (e.g. ZeroFox) do this.',                                                                                          '',     'TA07', 'D01'),
(43, 'F00044', 'Forensic analysis',                                         'Can be used in all phases for all techniques.',                                                                                                                                '',     'TA08', 'D01'),
(44, 'F00045', 'Forensic linguistic analysis',                              'Can be used in all phases for all techniques.',                                                                                                                                '',     'TA08', 'D01'),
(45, 'F00046', 'Pump priming analytics',                                    '',                                                                                                                                                                             '',     'TA08', 'D01'),
(46, 'F00047', 'Trace involved parties',                                    '',                                                                                                                                                                             '',     'TA08', 'D01'),
(47, 'F00048', 'Trace known operations and connections',                    '',                                                                                                                                                                             '',     'TA08', 'D01'),
(48, 'F00049', 'Trace money',                                               '',                                                                                                                                                                             '',     'TA08', 'D01'),
(49, 'F00050', 'Web cache analytics',                                       '',                                                                                                                                                                             '',     'TA08', 'D01'),
(50, 'F00051', 'Challenge expertise',                                       '',                                                                                                                                                                             '',     'TA09', 'D01'),
(51, 'F00052', 'Discover sponsors',                                         'Discovering the sponsors behind a campaign, narrative, bot, a set of accounts, or a social media comment.',                                                                    '',     'TA09', 'D01'),
(52, 'F00053', 'Government rumour control office',                          '',                                                                                                                                                                             '',     'TA09', 'D01'),
(53, 'F00054', 'Restrict people who can @ you on social networks',          '',                                                                                                                                                                             '',     'TA09', 'D01'),
(54, 'F00055', 'Verify credentials',                                        '',                                                                                                                                                                             '',     'TA09', 'D01'),
(55, 'F00056', 'Verify organisation legitimacy',                            '',                                                                                                                                                                             '',     'TA09', 'D01'),
(56, 'F00057', 'Verify personal credentials of experts',                   '',                                                                                                                                                                             '',     'TA09', 'D01'),
(57, 'F00058', 'Deplatform (cancel culture)',                               'Deplatform People: also includes attacking sources of funds, allies, followers.',                                                                                              '',     'TA10', 'D01'),
(58, 'F00059', 'Identify susceptible demographics',                         'All techniques provide or are susceptible to being countered by knowledge about user demographics.',                                                                           '',     'TA10', 'D01'),
(59, 'F00060', 'Identify susceptible influencers',                          'Find people of influence that might be targeted.',                                                                                                                             '',     'TA10', 'D01'),
(60, 'F00061', 'Microtargeting',                                            '',                                                                                                                                                                             '',     'TA10', 'D01'),
(61, 'F00062', 'Detect when Dormant account turns active',                  '',                                                                                                                                                                             '',     'TA11', 'D01'),
(62, 'F00063', 'Linguistic change analysis',                                '',                                                                                                                                                                             '',     'TA11', 'D01'),
(63, 'F00064', 'Monitor reports of account takeover',                       '',                                                                                                                                                                             '',     'TA11', 'D01'),
(64, 'F00065', 'Sentiment change analysis',                                 '',                                                                                                                                                                             '',     'TA11', 'D01'),
(65, 'F00066', 'Use language errors, time to respond to account bans and lawsuits, to indicate capabilities', '',                                                                                                                                           '',     'TA11', 'D01'),
(66, 'F00067', 'Data forensics',                                            '',                                                                                                                                                                             '',     '',     'D01'),
(67, 'F00068', 'Resonance analysis',                                        'A developing methodology for identifying statistical differences in how social groups use language.',                                                                          '',     '',     'D01'),
(68, 'F00069', 'Track Russian media and develop analytic methods',          'To effectively counter Russian propaganda, it will be critical to track Russian influence efforts.',                                                                           '',     '',     'D01'),
(69, 'F00070', 'Full spectrum analytics',                                   '',                                                                                                                                                                             '',     '',     'D01'),
(70, 'F00071', 'Network analysis: Identify/cultivate/support influencers',  'Local influencers detected via Twitter networks are likely local influencers in other online and off-line channels as well.',                                                  '',     '',     'D01'),
(71, 'F00072', 'Network analysis to identify central users in pro-Russia activist community','It is possible that some of these are bots or trolls.',                                                                                                       '',     '',     'D01'),
(72, 'F00073', 'Collect intel/recon on black/covert content creators/manipulators','Players at the level of covert attribution produce content on user-generated media.',                                                                                   '',     '',     'D01'),
(73, 'F00074', 'Identify relevant fence-sitter communities',                'Brand ambassador programmes could be used with influencers across a variety of social media channels.',                                                                        '',     '',     'D01'),
(74, 'F00075', 'Leverage open-source information',                          'Significant amounts of quality open-source information are now available.',                                                                                                    '',     '',     'D01'),
(75, 'F00076', 'Monitor/collect audience engagement data connected to useful idiots','Target audience connected to useful idiots.',                                                                                                                          '',     '',     'D01'),
(76, 'F00077', 'Model for bot account behaviour',                           'Bot account: action based, people.',                                                                                                                                           '',     'TA15', 'D01'),
(77, 'F00078', 'Monitor account level activity in social networks',         'All techniques benefit from careful analysis and monitoring of activities on social network.',                                                                                  '',     'TA15', 'D01'),
(78, 'F00079', 'Network anomaly detection',                                 '',                                                                                                                                                                             '',     'TA05', 'D01'),
(79, 'F00080', 'Hack the polls / content yourself',                         'If you hack your own polls, you learn how it could be done, and learn what to look for.',                                                                                      '',     'TA07', 'D01'),
(80, 'F00081', 'Need way for end user to report operations',                '',                                                                                                                                                                             '',     'TA09', 'D01'),
(81, 'F00082', 'Control the US slang translation boards',                   '',                                                                                                                                                                             '',     'TA11', 'D03'),
(82, 'F00083', 'Build and own meme generator, then track and watermark contents','',                                                                                                                                                                        '',     'TA11', 'D05'),
(83, 'F00084', 'Track individual bad actors',                               '',                                                                                                                                                                             '',     'TA15', 'D01'),
(84, 'F00085', 'Detection of a weak signal through global noise',           'Grey zone threats are challenging given that warning requires detection of a weak signal through global noise.',                                                                '',     '',     ''),
(85, 'F00086', 'Outpace Competitor Intelligence Capabilities',              'Develop an intelligence-based understanding of foreign actors motivations, psychologies, and societal and geopolitical contexts.',                                             '',     'TA02', 'D01'),
(86, 'F00087', 'Improve Indications and Warning',                           'United States has not adequately adapted its information indicators and thresholds for warning policymakers.',                                                                 '',     '',     'D01'),
(87, 'F00088', 'Revitalise an active measures working group',               'Recognise campaigns from weak signals, including rivals intent, capability, impact.',                                                                                          '',     '',     'D01'),
(88, 'F00089', 'Target/name/flag grey zone website content',                'Grey zone is second level of content producers and circulators, composed of outlets with uncertain attribution.',                                                              '',     'TA15', 'D01'),
(89, 'F00090', 'Match Punitive Tools with Third-Party Inducements',         'Bring private sector and civil society into accord on U.S. interests.',                                                                                                       '',     'TA01', 'D01'),
(90, 'F00091', 'Partner to develop analytic methods & tools',               'Working with relevant technology firms to ensure that contracted analytic support is available.',                                                                              '',     'TA01', 'D01'),
(91, 'F00092', 'Daylight',                                                  'Warn social media companies about an ongoing campaign (e.g. antivax sites).',                                                                                                 '',     'TA09', 'D01'),
(92, 'F00093', 'S4d detection and re-allocation approaches',                'S4D is a way to separate out different speakers in text, audio.',                                                                                                             'M004', 'TA15', 'D01'),
(93, 'F00094', 'Registries alert when large batches of newsy URLs get registered together','',                                                                                                                                                              'M003', 'TA07', 'D01'),
(94, 'F00095', 'Fact checking',                                             'Process suspicious artefacts, narratives, and incidents.',                                                                                                                    '',     'TA09', 'D01');

-- ============================================================
-- TABLE: incident
-- Real-world disinformation incidents and campaigns
-- ============================================================
DROP TABLE IF EXISTS `incident`;
CREATE TABLE `incident` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `objecttype` VARCHAR(50) NOT NULL DEFAULT '',
  `year_started` VARCHAR(10) NOT NULL DEFAULT '',
  `found_in_country` VARCHAR(200) NOT NULL DEFAULT '',
  `found_via` VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_incident_disarm_id` (`disarm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `incident` (`id`, `disarm_id`, `name`, `objecttype`, `year_started`, `found_in_country`, `found_via`) VALUES
(  1, 'I00001', 'Blacktivists facebook group',                                                      'incident',  '2016', 'USA',                       ''),
(  2, 'I00002', '#VaccinateUS',                                                                     'campaign',  '2014', 'World',                     ''),
(  3, 'I00003', 'Beyonce protest rallies',                                                          'incident',  '2016', 'USA',                       ''),
(  4, 'I00004', '#Macrongate',                                                                      'incident',  '2017', 'France',                    ''),
(  5, 'I00005', 'Brexit vote',                                                                      'campaign',  '2016', 'UK',                        ''),
(  6, 'I00006', 'Columbian Chemicals',                                                              'incident',  '2014', 'USA',                       ''),
(  7, 'I00007', 'Incirlik terrorists',                                                              'incident',  '2016', 'USA',                       ''),
(  8, 'I00008', 'Bujic',                                                                            'incident',  '2017', 'Serbia',                    ''),
(  9, 'I00009', 'PhilippinesExpert',                                                                'incident',  '2017', 'Philippines',               ''),
( 10, 'I00010', 'ParklandTeens',                                                                    'incident',  '2018', 'USA',                       ''),
( 11, 'I00011', 'CovingtonTeen',                                                                    'incident',  '2019', 'USA',                       ''),
( 12, 'I00012', 'ChinaSmog',                                                                        'incident',  '2011', 'China',                     ''),
( 13, 'I00013', 'FranceBlacktivists',                                                               'incident',  '2014', 'France',                    ''),
( 14, 'I00014', 'GiletsJaunePileon',                                                                'incident',  '2018', 'France',                    ''),
( 15, 'I00015', 'ConcordDiscovery',                                                                 'incident',  '2019', 'USA',                       ''),
( 16, 'I00016', 'LithuanianElves',                                                                  'campaign',  '2014', 'Lithuania',                 ''),
( 17, 'I00017', 'US presidential elections',                                                        'campaign',  '2016', 'USA',                       'OII'),
( 18, 'I00018', 'DNC email leak incident',                                                          'tactic',    '2016', 'USA',                       'OII'),
( 19, 'I00019', 'MacronTiphaine',                                                                   'incident',  '2017', 'France',                    'OII'),
( 20, 'I00020', '3000 tanks',                                                                       'incident',  '2017', 'World',                     'OII'),
( 21, 'I00021', 'Armenia elections',                                                                'campaign',  '2017', 'Armenia',                   'OII'),
( 22, 'I00022', '#Macronleaks',                                                                     'incident',  '2017', 'France',                    'OII'),
( 23, 'I00023', '#dislikemacron',                                                                   'incident',  '2017', 'France',                    'OII'),
( 24, 'I00024', '#syriahoax',                                                                       'incident',  '2017', 'USA',                       'OII'),
( 25, 'I00025', 'EU Army',                                                                          'incident',  '2018', 'EU',                        'OII'),
( 26, 'I00026', 'Netherlands referendum on Ukraine',                                                'incident',  '2016', 'Netherlands',               'OII'),
( 27, 'I00027', 'crucifiedboy',                                                                     'incident',  '2014', 'Ukraine',                   'OII'),
( 28, 'I00028', 'mh17 downed',                                                                      'incident',  '2014', 'Ukraine',                   'OII'),
( 29, 'I00029', 'MH17 investigation',                                                               'campaign',  '2016', 'Ukraine',                   'OII'),
( 30, 'I00030', 'LastJedi',                                                                         'incident',  '2018', 'World',                     'OII'),
( 31, 'I00031', 'antivax',                                                                          'apt',       '2018', 'World',                     'OII'),
( 32, 'I00032', 'Kavanaugh',                                                                        'incident',  '2018', 'USA',                       'OII'),
( 33, 'I00033', 'China 50cent Army',                                                                'apt',       '2014', 'China',                     'OII'),
( 34, 'I00034', 'DibaFacebookExpedition',                                                           'incident',  '2016', 'Taiwan',                    'OII'),
( 35, 'I00035', 'Brazilelections',                                                                  'campaign',  '2014', 'Brazil',                    'OII'),
( 36, 'I00036', 'BrazilPresDebate',                                                                 'incident',  '2014', 'Brazil',                    'OII'),
( 37, 'I00037', 'Rioelections',                                                                     'incident',  '2016', 'Brazil',                    'OII'),
( 38, 'I00038', 'Brazilimpeachment',                                                                'incident',  '2016', 'Brazil',                    'OII'),
( 39, 'I00039', 'MerkelFacebook',                                                                   'incident',  '2017', 'Germany',                   'OII'),
( 40, 'I00040', 'modamaniSelfie',                                                                   'incident',  '2015', 'Germany',                   'OII'),
( 41, 'I00041', 'Refugee crime map',                                                                'incident',  '2017', 'Germany',                   'OII'),
( 42, 'I00042', 'Saudi/Qatar bot dispute',                                                          'incident',  '2017', 'Qatar',                     'MIS'),
( 43, 'I00043', 'FCC comments',                                                                     'incident',  '2017', 'USA',                       'MIS'),
( 44, 'I00044', 'JadeHelm exercise',                                                                'incident',  '2015', 'USA',                       'MIS'),
( 45, 'I00045', 'Skripal',                                                                          'incident',  '2018', 'UK',                        ''),
( 46, 'I00046', 'North Macedonia',                                                                  'incident',  '2018', 'Macedonia',                 ''),
( 47, 'I00047', 'Sea of Azov',                                                                      'incident',  '2018', 'World',                     ''),
( 48, 'I00048', 'White Helmets',                                                                    'campaign',  '2015', 'World',                     ''),
( 49, 'I00049', 'White Helmets: Chemical Weapons',                                                  'incident',  '2017', 'World',                     ''),
( 50, 'I00050', '#HandsOffVenezuela',                                                               'incident',  '2019', 'World',                     ''),
( 51, 'I00051', 'Integrity Initiative',                                                             'incident',  '2018', 'World',                     ''),
( 52, 'I00052', 'China overview',                                                                   'campaign',  '2015', 'World',                     ''),
( 53, 'I00053', 'China Huawei CFO Arrest',                                                          'incident',  '2018', 'World',                     ''),
( 54, 'I00054', 'China Muslims',                                                                    'incident',  '2018', 'World',                     ''),
( 55, 'I00055', '50 Cent Army',                                                                     'campaign',  '2008', 'World',                     ''),
( 56, 'I00056', 'Iran Influence Operations',                                                        'campaign',  '2012', 'World',                     ''),
( 57, 'I00057', 'Mexico Election',                                                                  'incident',  '2018', 'Mexico',                    ''),
( 58, 'I00058', 'Chemnitz',                                                                         'incident',  '2018', 'Germany',                   ''),
( 59, 'I00059', 'Myanmar - Rohingya',                                                               'campaign',  '2014', 'Myanmar',                   ''),
( 60, 'I00060', 'White Genocide',                                                                   'campaign',  '2018', 'World',                     ''),
( 61, 'I00061', 'Military veterans Targeting',                                                      'campaign',  '2017', 'US',                        ''),
( 62, 'I00062', 'Brexit/UK ongoing',                                                                'campaign',  '2015', 'UK',                        ''),
( 63, 'I00063', 'Olympic Doping Scandal',                                                           'campaign',  '2016', 'World',                     ''),
( 64, 'I00064', 'Tinder nightmares: the promise and peril of political bots',                       'incident',  '2017', 'UK',                        ''),
( 65, 'I00065', 'Ghostwriter Influence Campaign',                                                   'campaign',  '2020', 'Lithuania, Latvia, Poland', ''),
( 66, 'I00066', 'The online war between Qatar and Saudi Arabia',                                    'incident',  '2017', 'Qatar',                     ''),
( 67, 'I00067', 'Understanding Information Disorder',                                               '',          '',     '',                          ''),
( 68, 'I00068', 'Attempted Audio Deepfake Call Targets LastPass Employee',                          '',          '',     '',                          ''),
( 69, 'I00069', 'Uncharmed: Untangling Iran''s APT42 Operations',                                  '',          '',     '',                          ''),
( 70, 'I00070', 'Eli Lilly Clarifies It''s Not Offering Free Insulin After Tweet From Fake Verified Account','', '',    '',                          ''),
( 71, 'I00071', 'Russia-aligned hacktivists stir up anti-Ukrainian sentiments in Poland',           '',          '',     '',                          ''),
( 72, 'I00072', 'Behind the Dutch Terror Threat Video: The St. Petersburg Troll Factory Connection','',          '',     '',                          ''),
( 73, 'I00073', 'Disinformation campaign removed by Facebook linked to Russia''s Internet Research Agency','',  '',     '',                          ''),
( 74, 'I00074', 'The Tactics & Tropes of the Internet Research Agency',                             '',          '',     '',                          ''),
( 75, 'I00075', 'How Russia Meddles Abroad for Profit: Cash, Trolls and a Cult Leader',             '',          '',     '',                          ''),
( 76, 'I00076', 'Network of Social Media Accounts Impersonates U.S. Political Candidates',         '',          '',     '',                          ''),
( 77, 'I00077', 'Fronts & Friends: An Investigation into Two Twitter Networks Linked to Russian Actors','',      '',     '',                          ''),
( 78, 'I00078', 'Meta''s September 2020 Removal of Coordinated Inauthentic Behavior',              '',          '',     '',                          ''),
( 79, 'I00079', 'Three thousand fake tanks',                                                        '',          '',     '',                          ''),
( 80, 'I00080', 'Hundreds Of Propaganda Accounts Targeting Iran And Qatar Have Been Removed From Facebook','',  '',     '',                          ''),
( 81, 'I00081', 'Belarus KGB created fake accounts to criticize Poland during border crisis',       '',          '',     '',                          ''),
( 82, 'I00082', 'Meta''s November 2021 Adversarial Threat Report',                                 '',          '',     '',                          ''),
( 83, 'I00083', 'Fake Think Tanks Fuel Fake News—And the President''s Tweets',                     '',          '',     '',                          ''),
( 84, 'I00084', 'Russia turns its diplomats into disinformation warriors',                          '',          '',     '',                          ''),
( 85, 'I00085', 'China''s large-scale media push: Attempts to influence Swedish media',             '',          '',     '',                          ''),
( 86, 'I00086', '#WeAreNotSafe – Exposing How a Post-October 7th Disinformation Network Operates', '',          '',     '',                          ''),
( 87, 'I00087', 'Challenging Truth and Trust: A Global Inventory of Organized Social Media Manipulation','',    '',     '',                          ''),
( 88, 'I00088', 'Much Ado About Somethings - China-Linked Influence Operation Endures Despite Takedown','',     '',     '',                          ''),
( 89, 'I00089', 'Hackers Use Fake Facebook Profiles of Attractive Women to Spread Viruses, Steal Passwords','', '',    '',                          ''),
( 90, 'I00090', 'Fake: US Intelligence Officer Says Poland Contributes to Ukraine''s Armed Forces Destruction','','',  '',                          ''),
( 91, 'I00091', 'Facebook uncovers Chinese network behind fake expert',                             '',          '',     '',                          ''),
( 92, 'I00092', 'The Most Influential Spreader of Coronavirus Misinformation Online',               '',          '',     '',                          ''),
( 93, 'I00093', 'China Falsely Denies Disinformation Campaign Targeting Canada''s Prime Minister',  '',          '',     '',                          ''),
( 94, 'I00094', 'A glimpse inside a Chinese influence campaign',                                    '',          '',     '',                          ''),
( 95, 'I00095', 'Meta: Chinese disinformation network was behind London front company recruiting content creators','','','',                          ''),
( 96, 'I00096', 'China ramps up use of AI misinformation',                                          '',          '',     '',                          ''),
( 97, 'I00097', 'Report: Not Just Algorithms',                                                      '',          '',     '',                          ''),
( 98, 'I00098', 'Gaming The System: How Extremists Exploit Gaming Sites',                           '',          '',     '',                          ''),
( 99, 'I00099', 'More Women Are Facing The Reality Of Deepfakes, And They''re Ruining Lives',       '',          '',     '',                          '');

-- ============================================================
-- TABLE: incident_technique
-- Many-to-many: which red techniques appear in each incident?
-- Populated from incident .md files; most incidents have no technique data yet.
-- Add rows here as incidents are annotated.
-- ============================================================
DROP TABLE IF EXISTS `incident_technique`;
CREATE TABLE `incident_technique` (
  `incident_id` VARCHAR(10) NOT NULL,
  `technique_id` VARCHAR(20) NOT NULL,
  `description` LONGTEXT NOT NULL DEFAULT '',
  PRIMARY KEY (`incident_id`, `technique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No rows yet — incident .md files contain empty technique tables.
-- Example of how to add future entries:
-- INSERT INTO `incident_technique` VALUES ('I00001', 'T0092', 'IRA created fake accounts…');

SET foreign_key_checks = 1;
