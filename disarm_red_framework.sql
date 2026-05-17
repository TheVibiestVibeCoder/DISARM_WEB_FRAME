-- DISARM Red Framework — Corrected MySQL Export
-- Hierarchy: Phases → Tactics → Techniques → Tasks
-- Import via phpMyAdmin > select your database > Import tab
--
-- INSTRUCTIONS:
--   1. Open phpMyAdmin and select your database
--   2. Click the "Import" tab
--   3. Upload this file and click "Go"
--   This script drops and recreates only the four core tables
--   (phase, tactic, technique, task) with correct Red Framework data.
--   All other tables (framework, incident, counter, etc.) are left intact.
--
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
-- TABLE: phase
-- ============================================================
DROP TABLE IF EXISTS `phase`;
CREATE TABLE `phase` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `rank` INT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `phase` (`id`, `disarm_id`, `name`, `rank`, `summary`) VALUES
(1, 'P01', 'Plan',    1, 'Envision the desired outcome. Lay out effective ways of achieving it.'),
(2, 'P02', 'Prepare', 2, 'Activities conducted before execution to improve the ability to conduct the action.'),
(3, 'P03', 'Execute', 3, 'Run the action, from initial exposure to wrap-up and/or maintaining presence.'),
(4, 'P04', 'Assess',  4, 'Evaluate effectiveness of action, for use in future plans.');

-- ============================================================
-- TABLE: tactic
-- ============================================================
DROP TABLE IF EXISTS `tactic`;
CREATE TABLE `tactic` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `phase_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `rank` INT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tactic` (`id`, `disarm_id`, `phase_id`, `name`, `rank`, `summary`) VALUES
-- P01 — Plan
( 1, 'TA01', 'P01', 'Plan Strategy',               1,  'Define the desired end state, i.e. the set of required conditions that defines achievement of all objectives.'),
( 2, 'TA02', 'P01', 'Plan Objectives',             2,  'Set clearly defined, measurable, and achievable objectives. The objective statement should not specify the way and means of accomplishment but rather the goal the threat actor wishes to achieve.'),
( 3, 'TA13', 'P01', 'Target Audience Analysis',    3,  'Identifying and analysing the target audience examines target audience member locations, political affiliations, financial situations, and other attributes.'),
-- P02 — Prepare
( 4, 'TA05', 'P02', 'Microtarget',                 4,  'Actions taken which help target content to specific audiences identified and analysed as part of TA13: Target Audience Analysis.'),
( 5, 'TA06', 'P02', 'Develop Content',             5,  'Create or acquire text, images, and other content.'),
( 6, 'TA07', 'P02', 'Select Channels & Affordances', 6, 'Selecting platforms and affordances assesses which online or offline platforms and their associated affordances maximise an influence operation''s ability to reach its target audience.'),
( 7, 'TA14', 'P02', 'Develop Narratives',          7,  'The promotion of beneficial master narratives is perhaps the most effective method for achieving long-term strategic narrative dominance.'),
( 8, 'TA15', 'P02', 'Establish Assets',            8,  'Establishing information assets generates messaging tools, including social media accounts, operation personnel, and organisations.'),
( 9, 'TA16', 'P02', 'Establish Legitimacy',        9,  'Establish assets that create trust.'),
-- P03 — Execute
(10, 'TA08', 'P03', 'Conduct Pump Priming',        10, 'Release content on a targeted small scale, prior to general release. Used for preparation before broader release and as message honing.'),
(11, 'TA09', 'P03', 'Deliver Content',             11, 'Release content to general public or larger population.'),
(12, 'TA10', 'P03', 'Drive Offline Activity',      12, 'Move incident/campaign from online to offline. Encouraging users to engage in the physical information space or offline world.'),
(13, 'TA11', 'P03', 'Persist in Information Env.', 13, 'Taking measures that allow an operation to maintain its presence and avoid takedown by an external entity.'),
(14, 'TA17', 'P03', 'Maximise Exposure',           14, 'Maximise exposure of the target audience to incident/campaign content via flooding, amplifying, and cross-posting.'),
(15, 'TA18', 'P03', 'Drive Online Harms',          15, 'Actions taken by an influence operation to harm their opponents in online spaces through harassment, suppression, and controlling the information space.'),
-- P04 — Assess
(16, 'TA12', 'P04', 'Assess Effectiveness',        16, 'Assess effectiveness of action, for use in future plans.');

-- ============================================================
-- TABLE: technique
-- Note: sub-techniques are identified by a dot in disarm_id
--       e.g. T0074.001 is a sub-technique of T0074
-- ============================================================
DROP TABLE IF EXISTS `technique`;
CREATE TABLE `technique` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(20) NOT NULL,
  `tactic_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `technique` (`id`, `disarm_id`, `tactic_id`, `name`, `summary`) VALUES
-- TA01: Plan Strategy
(  1, 'T0073',     'TA01', 'Determine Target Audiences',        'Determining the target audiences (segments of the population) who will receive campaign narratives and artefacts intended to achieve the strategic ends.'),
(  2, 'T0074',     'TA01', 'Determine Strategic Ends',          'These are the long-term end-states the campaign aims to bring about. They typically involve an advantageous position vis-à-vis competitors in terms of power or influence.'),
(  3, 'T0074.001', 'TA01', 'Geopolitical Advantage',            'Favourable position on the international stage in terms of great power politics or regional rivalry.'),
(  4, 'T0074.002', 'TA01', 'Domestic Political Advantage',      'Favourable position vis-à-vis national or sub-national political opponents.'),
(  5, 'T0074.003', 'TA01', 'Economic Advantage',                'Favourable position domestically or internationally in the realms of commerce, trade, finance, industry.'),
(  6, 'T0074.004', 'TA01', 'Ideological Advantage',             'Favourable position domestically or internationally in the market for ideas, beliefs, and world views.'),
-- TA02: Plan Objectives
(  7, 'T0002',     'TA02', 'Facilitate State Propaganda',       'Organise citizens around pro-state messaging. Coordinate paid or volunteer groups to push state propaganda.'),
(  8, 'T0066',     'TA02', 'Degrade Adversary',                 'Plan to degrade an adversary''s image or ability to act. This could include preparation and use of harmful information about the adversary.'),
(  9, 'T0075',     'TA02', 'Dismiss',                           'Push back against criticism by dismissing your critics. Argue that the critics use a different standard or that their criticism is biased.'),
( 10, 'T0075.001', 'TA02', 'Discredit Credible Sources',        'Plan to delegitimize the media landscape and degrade public trust in reporting, by discrediting credible sources.'),
( 11, 'T0076',     'TA02', 'Distort',                           'Twist the narrative. Take information, or artefacts like images, and change the framing around them.'),
( 12, 'T0077',     'TA02', 'Distract',                          'Shift attention to a different narrative or actor, for instance by accusing critics of the same activity.'),
( 13, 'T0078',     'TA02', 'Dismay',                            'Threaten the critic or narrator of events. For instance, threaten journalists or news outlets reporting on a story.'),
( 14, 'T0079',     'TA02', 'Divide',                            'Create conflict between subgroups, to widen divisions in a community.'),
( 15, 'T0135',     'TA02', 'Undermine',                         'Weaken, debilitate, or subvert a target or their actions. May include disparaging, sabotaging, or thwarting an opponent.'),
( 16, 'T0135.001', 'TA02', 'Smear',                             'Denigrate, disparage, or discredit an opponent. A common tactical objective in political campaigns.'),
( 17, 'T0135.002', 'TA02', 'Thwart',                            'Prevent the successful outcome of a policy, operation, or initiative.'),
( 18, 'T0135.003', 'TA02', 'Subvert',                           'Sabotage, destroy, or damage a system, process, or relationship.'),
( 19, 'T0135.004', 'TA02', 'Polarise',                          'Cause a target audience to divide into two completely opposing groups.'),
( 20, 'T0136',     'TA02', 'Cultivate Support',                 'Grow or maintain the base of support for the actor, ally, or action, including reputation management and public relations.'),
( 21, 'T0136.001', 'TA02', 'Defend Reputation',                 'Preserve a positive perception in the public''s mind following an accusation or adverse event.'),
( 22, 'T0136.002', 'TA02', 'Justify Action',                    'Convince others to exonerate you of a perceived wrongdoing.'),
( 23, 'T0136.003', 'TA02', 'Energise Supporters',               'Raise the morale of those who support the organisation or group. Invigorate constituents with zeal for the mission.'),
( 24, 'T0136.004', 'TA02', 'Boost Reputation',                  'Elevate the estimation of the actor in the public''s mind. Improve their image or standing.'),
( 25, 'T0136.005', 'TA02', 'Cultivate Support for Initiative',  'Elevate or fortify the public backing for a policy, operation, or idea.'),
( 26, 'T0136.006', 'TA02', 'Cultivate Support for Ally',        'Elevate or fortify the public backing for a partner.'),
( 27, 'T0136.007', 'TA02', 'Recruit Members',                   'Motivate followers to join or subscribe as members of the team.'),
( 28, 'T0136.008', 'TA02', 'Increase Prestige',                 'Improve personal standing within a community. Gain fame, approbation, or notoriety.'),
( 29, 'T0137',     'TA02', 'Make Money',                        'Profit from disinformation, conspiracy theories, or online harm. May also be a way to sustain a political campaign.'),
( 30, 'T0137.001', 'TA02', 'Generate Ad Revenue',               'Earn income from digital advertisements published alongside inauthentic content.'),
( 31, 'T0137.002', 'TA02', 'Scam',                              'Defraud a target or trick a target into doing something that benefits the attacker.'),
( 32, 'T0137.003', 'TA02', 'Raise Funds',                       'Solicit donations for a cause. Popular conspiracy theorists can attract financial contributions from followers.'),
( 33, 'T0137.004', 'TA02', 'Sell Items under False Pretences',  'Offer products for sale under false pretences. Campaigns may hijack or create causes built on disinformation.'),
( 34, 'T0137.005', 'TA02', 'Extort',                            'Coerce money or favours from a target by threatening to expose or corrupt information.'),
( 35, 'T0137.006', 'TA02', 'Manipulate Stocks',                 'Artificially inflate or deflate the price of stocks or other financial instruments.'),
( 36, 'T0138',     'TA02', 'Motivate to Act',                   'Persuade, impel, or provoke the target to behave in a specific manner favourable to the attacker.'),
( 37, 'T0138.001', 'TA02', 'Encourage',                         'Inspire, animate, or exhort a target to act using propaganda, disinformation, or conspiracy theories.'),
( 38, 'T0138.002', 'TA02', 'Provoke',                           'Instigate, incite, or arouse a target to act. Social media manipulators exploit moral outrage.'),
( 39, 'T0138.003', 'TA02', 'Compel',                            'Force target to take an action or to stop taking an action it has already started.'),
( 40, 'T0139',     'TA02', 'Dissuade from Acting',              'Discourage, deter, or inhibit the target from actions which would be unfavourable to the attacker.'),
( 41, 'T0139.001', 'TA02', 'Discourage',                        'Make a target disinclined or reluctant to act.'),
( 42, 'T0139.002', 'TA02', 'Silence',                           'Intimidate or incentivise target into remaining silent or prevent target from speaking out.'),
( 43, 'T0139.003', 'TA02', 'Deter',                             'Prevent target from taking an action for fear of the consequences.'),
( 44, 'T0140',     'TA02', 'Cause Harm',                        'Persecute, malign, or inflict pain upon a target. Objective may be to cause fear or emotional distress.'),
( 45, 'T0140.001', 'TA02', 'Defame',                            'Attempt to damage the target''s personal reputation by impugning their character.'),
( 46, 'T0140.002', 'TA02', 'Intimidate',                        'Coerce, bully, or frighten the target. An influence operation may use intimidation to compel the target.'),
( 47, 'T0140.003', 'TA02', 'Spread Hate',                       'Publish and/or propagate demeaning content targeting an individual or group with intent to cause distress.'),
-- TA13: Target Audience Analysis
( 48, 'T0072',     'TA13', 'Segment Audiences',                    'Create audience segmentations by features of interest including political affiliation, geographic location, income, demographics, and psychographics.'),
( 49, 'T0072.001', 'TA13', 'Geographic Segmentation',              'Target populations in a specific geographic location, such as a region, state, or city.'),
( 50, 'T0072.002', 'TA13', 'Demographic Segmentation',             'Target populations based on demographic segmentation, including age, gender, and income.'),
( 51, 'T0072.003', 'TA13', 'Economic Segmentation',                'Target populations based on their income bracket, wealth, or other financial division.'),
( 52, 'T0072.004', 'TA13', 'Psychographic Segmentation',           'Target populations based on psychographic segmentation using audience values and decision-making processes.'),
( 53, 'T0072.005', 'TA13', 'Political Segmentation',               'Target populations based on their political affiliations, especially when aiming to manipulate voting or change policy.'),
( 54, 'T0080',     'TA13', 'Map Target Audience Info. Env.',        'Mapping the information environment analyses social media analytics, web traffic, and media surveys to determine most accessible channels.'),
( 55, 'T0080.001', 'TA13', 'Monitor Social Media Analytics',       'Use social media analytics to determine which factors increase content exposure to the target audience.'),
( 56, 'T0080.002', 'TA13', 'Evaluate Media Surveys',               'Evaluate own or third-party media surveys to determine what type of content appeals to the target audience.'),
( 57, 'T0080.003', 'TA13', 'Identify Trending Topics/Hashtags',    'Identify trending hashtags on social media platforms for later use in boosting operation content.'),
( 58, 'T0080.004', 'TA13', 'Conduct Web Traffic Analysis',         'Determine which search engines, keywords, websites, and advertisements gain most traction with the target audience.'),
( 59, 'T0080.005', 'TA13', 'Assess Degree/Type of Media Access',   'Survey a target audience''s Internet availability and degree of media freedom to determine content accessibility.'),
( 60, 'T0081',     'TA13', 'Identify Social/Technical Vuln.',       'Identifying social and technical vulnerabilities determines weaknesses within the target audience information environment for later exploitation.'),
( 61, 'T0081.001', 'TA13', 'Find Echo Chambers',                   'Find or plan to create areas where individuals only engage with people they agree with.'),
( 62, 'T0081.002', 'TA13', 'Identify Data Voids',                  'A data void refers to a word or phrase that results in little, manipulative, or low-quality search engine data.'),
( 63, 'T0081.003', 'TA13', 'Identify Existing Prejudices',         'Exploit existing racial, religious, demographic, or social prejudices to further polarise the target audience.'),
( 64, 'T0081.004', 'TA13', 'Identify Existing Fissures',           'Identify existing fissures to pit target populations against one another.'),
( 65, 'T0081.005', 'TA13', 'Identify Conspiracy Narratives',       'Assess preexisting conspiracy theories or suspicions in a population to identify existing narratives that support operational objectives.'),
( 66, 'T0081.006', 'TA13', 'Identify Wedge Issues',                'Exploit wedge issues by intentionally polarising the public along the wedge issue line.'),
( 67, 'T0081.007', 'TA13', 'Identify Target Audience Adversaries', 'Identify or create a real or imaginary adversary to centre operation narratives against.'),
( 68, 'T0081.008', 'TA13', 'Identify Media System Vulnerabilities','Exploit existing weaknesses in a target''s media system including biases among media agencies.'),
-- TA05: Microtarget
( 69, 'T0016',     'TA05', 'Create Clickbait',                  'Create attention grabbing headlines (outrage, doubt, humour) required to drive traffic and engagement.'),
( 70, 'T0018',     'TA05', 'Purchase Targeted Advertisements',  'Create or fund advertisements targeted at specific populations.'),
( 71, 'T0101',     'TA05', 'Create Localised Content',          'Content that appeals to a specific community, often in defined geographic areas, using local language and dialects.'),
( 72, 'T0102',     'TA05', 'Leverage Echo Chambers',            'Create isolated areas of the internet by aggregating individuals into a single target audience based on shared interests.'),
( 73, 'T0102.001', 'TA05', 'Use Existing Echo Chambers',        'Use existing Echo Chambers/Filter Bubbles.'),
( 74, 'T0102.002', 'TA05', 'Create Echo Chambers',              'Create new Echo Chambers/Filter Bubbles.'),
( 75, 'T0102.003', 'TA05', 'Exploit Data Voids',                'Exploit data voids — words or phrases that result in little, manipulative, or low-quality search engine data.'),
-- TA06: Develop Content
( 76, 'T0015',     'TA06', 'Create Hashtags & Search Artefacts','Create one or more hashtags and/or hashtag groups to create a perception of reality and publicise the story more widely.'),
( 77, 'T0015.001', 'TA06', 'Use Existing Hashtag',              'Use a dedicated, existing hashtag for the campaign/incident.'),
( 78, 'T0015.002', 'TA06', 'Create New Hashtag',                'Create a campaign/incident specific hashtag.'),
( 79, 'T0023',     'TA06', 'Distort Facts',                     'Change, twist, or exaggerate existing facts to construct a narrative that differs from reality.'),
( 80, 'T0023.001', 'TA06', 'Reframe Context',                   'Remove an event from its surrounding context to distort its intended meaning.'),
( 81, 'T0023.002', 'TA06', 'Edit Open-Source Content',          'Edit open-source content, such as collaborative blogs or encyclopaedias, to promote narratives on outlets with existing credibility.'),
( 82, 'T0084',     'TA06', 'Reuse Existing Content',            'Recycle content from own previous operations or plagiarise from external operations.'),
( 83, 'T0084.001', 'TA06', 'Use Copypasta',                     'Copy and paste text that has been widely shared across various online platforms.'),
( 84, 'T0084.002', 'TA06', 'Plagiarise Content',                'Take content from other sources without proper attribution.'),
( 85, 'T0084.003', 'TA06', 'Deceptively Labelled/Translated',   'Take authentic content from other sources and add deceptive labels or deceptively translate the content.'),
( 86, 'T0084.004', 'TA06', 'Appropriate Content',               'Take content from other sources with proper attribution but leverage it as disinformation.'),
( 87, 'T0085',     'TA06', 'Develop Text-Based Content',        'Creating and editing false or misleading text-based artefacts for use in a disinformation campaign.'),
( 88, 'T0085.001', 'TA06', 'Develop AI-Generated Text',         'AI-generated texts composed by computers using text-generating AI technology.'),
( 89, 'T0085.003', 'TA06', 'Develop Inauthentic News Articles', 'Develop false or misleading news articles aligned to campaign goals or narratives.'),
( 90, 'T0085.004', 'TA06', 'Develop Document',                  'Produce text in the form of a document.'),
( 91, 'T0085.005', 'TA06', 'Develop Book',                      'Produce text content in the form of a book (e-book or physical).'),
( 92, 'T0085.006', 'TA06', 'Develop Opinion Article',           'Opinion articles (Op-Eds) posted to news sources, exploited to promote narratives.'),
( 93, 'T0085.007', 'TA06', 'Create Fake Research',              'Create fake academic research targeting hot-button issues such as gender, race, climate science, etc.'),
( 94, 'T0085.008', 'TA06', 'Machine Translated Text',           'Text translated into another language using machine translation tools.'),
( 95, 'T0086',     'TA06', 'Develop Image-Based Content',       'Creating and editing false or misleading visual artefacts for use in a disinformation campaign.'),
( 96, 'T0086.001', 'TA06', 'Develop Memes',                     'Memes are a powerful tool and the heart of modern influence campaigns — pulling together reference, commentary, image, narrative, emotion and message.'),
( 97, 'T0086.002', 'TA06', 'Develop AI-Generated Images',       'Deepfakes: AI-generated falsified photos depicting an inauthentic situation.'),
( 98, 'T0086.003', 'TA06', 'Deceptively Edit Images',           'Cheap fakes using less sophisticated measures of altering an image to create false context.'),
( 99, 'T0086.004', 'TA06', 'Aggregate Info into Evidence Collages', 'Image files that aggregate positive evidence.'),
(100, 'T0087',     'TA06', 'Develop Video-Based Content',       'Creating and editing false or misleading video artefacts for use in a disinformation campaign.'),
(101, 'T0087.001', 'TA06', 'Develop AI-Generated Videos',       'Deepfake videos: AI-generated falsified videos synthetically recreating an individual''s face, body, voice, and gestures.'),
(102, 'T0087.002', 'TA06', 'Deceptively Edit Video',            'Cheap fakes: slowing, speeding, or cutting footage to create false context.'),
(103, 'T0088',     'TA06', 'Develop Audio-Based Content',       'Creating and editing false or misleading audio artefacts for use in a disinformation campaign.'),
(104, 'T0088.001', 'TA06', 'Develop AI-Generated Audio',        'Deepfake audio: AI-generated falsified soundbites synthetically recreating an individual''s voice.'),
(105, 'T0088.002', 'TA06', 'Deceptively Edit Audio',            'Cheap fakes: altering audio to create false context.'),
(106, 'T0089',     'TA06', 'Obtain Private Documents',          'Procuring documents that are not publicly available, whether authentic, altered, or inauthentic.'),
(107, 'T0089.001', 'TA06', 'Obtain Authentic Documents',        'Procure authentic non-public documents for later use in the operation.'),
(108, 'T0089.003', 'TA06', 'Alter Authentic Documents',         'Alter authentic documents (public or non-public) to achieve campaign goals.'),
-- TA07: Select Channels & Affordances
(109, 'T0029',     'TA07', 'Online Polls',                      'Create fake online polls, or manipulate existing online polls. Data gathering tactic to target those who engage.'),
(110, 'T0107',     'TA07', 'Bookmarking & Content Curation',    'Platforms for searching, sharing, and curating content and media (e.g. Pinterest, Flipboard).'),
(111, 'T0109',     'TA07', 'Consumer Review Networks',          'Platforms for finding, reviewing, and sharing information about brands, products, services (e.g. Yelp, TripAdvisor).'),
(112, 'T0110',     'TA07', 'Formal Diplomatic Channels',        'Leveraging formal diplomatic channels to communicate with foreign governments.'),
(113, 'T0111',     'TA07', 'Traditional Media',                 'Examples include TV, Newspaper, Radio, etc.'),
(114, 'T0111.001', 'TA07', 'TV',                                'Television as a channel.'),
(115, 'T0111.002', 'TA07', 'Newspaper',                         'Newspaper as a channel.'),
(116, 'T0111.003', 'TA07', 'Radio',                             'Radio as a channel.'),
-- TA14: Develop Narratives
(117, 'T0003',     'TA14', 'Leverage Existing Narratives',           'Use or adapt existing narrative themes. New information is understood through the lens of prevailing narratives.'),
(118, 'T0004',     'TA14', 'Develop Competing Narratives',           'Advance competing narratives connected to same issue, e.g. simultaneously deny while dismissing.'),
(119, 'T0022',     'TA14', 'Leverage Conspiracy Theory Narratives',  'Conspiracy narratives appeal to the human desire for explanatory order, invoking powerful actors in pursuit of political goals.'),
(120, 'T0022.001', 'TA14', 'Amplify Existing Conspiracy Narratives', 'Amplify an existing conspiracy theory narrative that aligns with incident or campaign goals.'),
(121, 'T0022.002', 'TA14', 'Develop Original Conspiracy Narratives', 'Develop original conspiracy theory narratives for greater control and alignment over the narrative.'),
(122, 'T0040',     'TA14', 'Demand Insurmountable Proof',            'Constantly escalate demands for proof, leveraging the asymmetric advantage that conspiracists have over truth-tellers.'),
(123, 'T0068',     'TA14', 'Respond to Breaking News/Active Crisis', 'Media attention is heightened during breaking news, where unclear facts increase speculation vulnerable to manipulation.'),
(124, 'T0082',     'TA14', 'Develop New Narratives',                 'Develop new narratives to further strategic or tactical goals when existing narratives don''t align.'),
(125, 'T0083',     'TA14', 'Integrate Audience Vuln. into Narrative','Exploit preexisting weaknesses, fears, and enemies of the target audience for integration into the operation''s narratives.'),
-- TA15: Establish Assets
(126, 'T0010',     'TA15', 'Cultivate Ignorant Agents',               'Cultivate propagandists for a cause whose goals are not fully comprehended. Also known as "useful idiots" or "unwitting agents".'),
(127, 'T0014',     'TA15', 'Prepare Fundraising Campaigns',           'Systematic effort to seek financial support while further promoting operation information pathways.'),
(128, 'T0014.001', 'TA15', 'Raise Funds from Malign Actors',          'Contributions from foreign agents, cutouts or proxies, shell companies, dark money groups, etc.'),
(129, 'T0014.002', 'TA15', 'Raise Funds from Ignorant Agents',        'Scams, donations intended for one stated purpose but used for another.'),
(130, 'T0065',     'TA15', 'Prepare Physical Broadcast Capabilities', 'Create or coopt broadcast capabilities (e.g. TV, radio).'),
(131, 'T0091',     'TA15', 'Recruit Malign Actors',                   'Operators recruit bad actors including trolls, partisans, and contractors.'),
(132, 'T0091.001', 'TA15', 'Recruit Contractors',                     'Operators recruit paid contractors to support the campaign.'),
(133, 'T0091.002', 'TA15', 'Recruit Partisans',                       'Operators recruit partisans (ideologically-aligned individuals) to support the campaign.'),
(134, 'T0091.003', 'TA15', 'Enlist Troll Accounts',                   'Hire trolls — human operators of fake accounts that aim to provoke others by posting and amplifying controversial content.'),
(135, 'T0092',     'TA15', 'Build Network',                           'Build own network, creating links between accounts to amplify and promote narratives and artefacts.'),
(136, 'T0092.001', 'TA15', 'Create Organisations',                    'Establish organisations with legitimate or falsified hierarchies to structure operation assets.'),
(137, 'T0092.002', 'TA15', 'Use Follow Trains',                       'A group of people who follow each other to grow social media following.'),
(138, 'T0092.003', 'TA15', 'Create Community or Sub-Group',           'Create a new community or sub-group when none exists that meets campaign goals.'),
(139, 'T0093',     'TA15', 'Acquire/Recruit Network',                 'Acquire an existing network by paying, recruiting, or exerting control over the leaders of the network.'),
(140, 'T0093.001', 'TA15', 'Fund Proxies',                            'Fund external entities that work for the operation to diversify locations and complicate attribution.'),
(141, 'T0093.002', 'TA15', 'Acquire Botnets',                         'A botnet is a group of bots that can function in coordination with each other.'),
(142, 'T0094',     'TA15', 'Infiltrate Existing Networks',            'Deceptively insert social assets into existing networks to influence members and the wider information environment.'),
(143, 'T0094.001', 'TA15', 'Identify Susceptible Targets in Networks','Identify individuals and groups that might be susceptible to being co-opted or influenced.'),
(144, 'T0094.002', 'TA15', 'Utilise Butterfly Attacks',               'Pretend to be members of a certain social group to insert controversial statements into the discourse.'),
(145, 'T0095',     'TA15', 'Develop Owned Media Assets',              'Owned media assets include websites, blogs, social media pages, forums, and other platforms.'),
(146, 'T0096',     'TA15', 'Leverage Content Farms',                  'Using large-scale content providers for creating and amplifying campaign artefacts at scale.'),
(147, 'T0096.001', 'TA15', 'Create Content Farms',                    'Create an organisation for creating and amplifying campaign artefacts at scale.'),
(148, 'T0096.002', 'TA15', 'Outsource Content Creation',              'Outsource content creation to external companies to avoid attribution or improve quality.'),
(149, 'T0113',     'TA15', 'Employ Commercial Analytic Firms',        'Commercial analytic firms evaluate data to detect trends, complicating attribution while tailoring content to preferences.'),
-- TA16: Establish Legitimacy
(150, 'T0097',     'TA16', 'Present Persona',                     'Different types of personas commonly taken on by threat actors during influence operations, from individual to institutional.'),
(151, 'T0097.100', 'TA16', 'Individual Persona',                  'Presenting as an individual.'),
(152, 'T0097.101', 'TA16', 'Local Persona',                       'Presents as living in a particular geography or having local knowledge relevant to a narrative.'),
(153, 'T0097.102', 'TA16', 'Journalist Persona',                  'Presents as a reporter or journalist delivering news, conducting interviews, investigations.'),
(154, 'T0097.103', 'TA16', 'Activist Persona',                    'Presents as an activist who campaigns for a political cause.'),
(155, 'T0097.104', 'TA16', 'Hacktivist Persona',                  'Presents as an activist who conducts offensive cyber operations for political purposes.'),
(156, 'T0097.105', 'TA16', 'Military Personnel Persona',          'Presents as a serving member or veteran of a military organisation.'),
(157, 'T0097.106', 'TA16', 'Recruiter Persona',                   'Presents as a potential employer or provider of freelance work.'),
(158, 'T0097.107', 'TA16', 'Researcher Persona',                  'Presents as conducting research for academic institutions or think tanks.'),
(159, 'T0097.108', 'TA16', 'Expert Persona',                      'Presents as having expertise or experience in a field to add credibility to narratives.'),
(160, 'T0097.109', 'TA16', 'Romantic Suitor Persona',             'Presents as seeking a romantic or physical connection — used for blackmail, information extraction, or financial fraud.'),
(161, 'T0097.110', 'TA16', 'Party Official Persona',              'Presents as an official member of a political party.'),
(162, 'T0097.111', 'TA16', 'Government Official Persona',         'Presents as an active or previous government official.'),
(163, 'T0097.112', 'TA16', 'Government Employee Persona',         'Presents as an active or previous civil servant.'),
(164, 'T0097.200', 'TA16', 'Institutional Persona',               'Presenting as an institution, encompassing various organisational types.'),
(165, 'T0097.201', 'TA16', 'Local Institution Persona',           'Presenting as operating in a particular geography or having local knowledge.'),
(166, 'T0097.202', 'TA16', 'News Outlet Persona',                 'Presents as an organisation which delivers news to its target audience.'),
(167, 'T0097.203', 'TA16', 'Fact Checking Organisation Persona',  'Presents as an organisation which assesses the validity of others'' reporting.'),
(168, 'T0097.204', 'TA16', 'Think Tank Persona',                  'Presents as a think tank conducting original research and proposing new policies.'),
(169, 'T0097.205', 'TA16', 'Business Persona',                    'Presents as a for-profit organisation which provides goods or services.'),
(170, 'T0097.206', 'TA16', 'Government Institution Persona',      'Presenting as a government or government ministry.'),
(171, 'T0097.207', 'TA16', 'NGO Persona',                         'Presents as a Non-Governmental Organisation providing services or advocating for public policy.'),
(172, 'T0097.208', 'TA16', 'Social Cause Persona',                'Presents as an account focused on a social cause, exploiting emotional investment.'),
(173, 'T0098',     'TA16', 'Establish Inauthentic News Sites',    'Modern computational propaganda uses imposter news sites with superficial markers of authenticity.'),
(174, 'T0098.001', 'TA16', 'Create Inauthentic News Sites',       'Create new inauthentic news sites.'),
(175, 'T0098.002', 'TA16', 'Leverage Existing Inauthentic Sites', 'Leverage existing inauthentic news sites.'),
(176, 'T0100',     'TA16', 'Co-Opt Trusted Sources',              'Infiltrate or repurpose a source to reach a target audience through existing reliable networks.'),
(177, 'T0100.001', 'TA16', 'Co-Opt Trusted Individuals',          'Co-opt trusted individuals to legitimise operation narratives.'),
(178, 'T0100.002', 'TA16', 'Co-Opt Grassroots Groups',            'Co-opt grassroots groups.'),
(179, 'T0100.003', 'TA16', 'Co-Opt Influencers',                  'Co-opt social media influencers.'),
(180, 'T0143',     'TA16', 'Persona Legitimacy',                  'Assess whether an account is presenting an authentic, fabricated, or parody persona.'),
(181, 'T0143.001', 'TA16', 'Authentic Persona',                   'An individual or institution presenting a persona that legitimately matches who or what they are.'),
(182, 'T0143.002', 'TA16', 'Fabricated Persona',                  'An individual or institution pretending to have a persona without any legitimate claim to it.'),
(183, 'T0143.003', 'TA16', 'Impersonated Persona',                'Threat actors impersonate existing individuals or institutions to conceal identity or add legitimacy.'),
(184, 'T0143.004', 'TA16', 'Parody Persona',                      'A form of artistic expression that imitates the style of a particular individual — may be exploited to spread campaign content.'),
-- TA08: Conduct Pump Priming
(185, 'T0020',     'TA08', 'Trial Content',                 'Iteratively test incident performance, e.g. A/B test headline/content engagement metrics.'),
(186, 'T0042',     'TA08', 'Seed Kernel of Truth',          'Wrap lies or altered context/facts around truths to make messaging less likely to be dismissed out of hand.'),
(187, 'T0044',     'TA08', 'Seed Distortions',              'Try a wide variety of messages in the early hours surrounding an incident to give a misleading account.'),
(188, 'T0045',     'TA08', 'Use Fake Experts',              'Use fake experts set up during Establish Legitimacy. Pseudo-experts give "credibility" to misinformation.'),
(189, 'T0046',     'TA08', 'Use Search Engine Optimisation','Manipulate content engagement metrics to influence news search results. Also known as "Black-hat SEO".'),
-- TA09: Deliver Content
(190, 'T0114',     'TA09', 'Deliver Ads',                         'Delivering content via any form of paid media or advertising.'),
(191, 'T0114.001', 'TA09', 'Social Media Ads',                    'Deliver ads through social media platforms.'),
(192, 'T0114.002', 'TA09', 'Traditional Media Ads',               'Deliver ads through TV, Radio, Newspaper, billboards.'),
(193, 'T0115',     'TA09', 'Post Content',                        'Delivering content by posting via owned media (assets that the operator controls).'),
(194, 'T0115.001', 'TA09', 'Share Memes',                         'Share memes — a powerful tool at the heart of modern influence campaigns.'),
(195, 'T0115.002', 'TA09', 'Post Violative Content',              'Post violative content to provoke takedown and backlash.'),
(196, 'T0115.003', 'TA09', 'One-Way Direct Posting',              'Post via a one-way messaging service, without allowing opportunities for fact-checking or disagreement.'),
(197, 'T0116',     'TA09', 'Comment or Reply on Content',         'Delivering content by replying or commenting via owned media.'),
(198, 'T0116.001', 'TA09', 'Post Inauthentic Social Media Comment','Use government-paid social media commenters, astroturfers, or chat bots to influence online conversations.'),
(199, 'T0117',     'TA09', 'Attract Traditional Media',           'Deliver content by attracting the attention of traditional media (earned media).'),
-- TA10: Drive Offline Activity
(200, 'T0017',     'TA10', 'Conduct Fundraising',             'Systematic effort to seek financial support while further promoting operation information pathways.'),
(201, 'T0017.001', 'TA10', 'Conduct Crowdfunding Campaigns',  'Crowdfunding campaigns on platforms such as GoFundMe, GiveSendGo, Tipeee, Patreon.'),
(202, 'T0057',     'TA10', 'Organise Events',                 'Coordinate and promote real-world events across media platforms, e.g. rallies, protests, gatherings.'),
(203, 'T0057.001', 'TA10', 'Pay for Physical Action',         'Pay individuals to act in the physical realm to create specific situations supporting operation narratives.'),
(204, 'T0057.002', 'TA10', 'Conduct Symbolic Action',         'Activities specifically intended to advance the operation''s narrative by signalling something to the audience.'),
(205, 'T0061',     'TA10', 'Sell Merchandise',                'Get the message or narrative into physical space in the offline world while making money.'),
(206, 'T0126',     'TA10', 'Encourage Attendance at Events',  'Operation encourages attendance at existing real world events.'),
(207, 'T0126.001', 'TA10', 'Call to Action to Attend',        'Call to action to attend an event.'),
(208, 'T0126.002', 'TA10', 'Facilitate Logistics/Support',    'Facilitate logistics or support for travel, food, housing, etc.'),
(209, 'T0127',     'TA10', 'Physical Violence',               'Use of force to injure, abuse, damage, or destroy to discourage opponents or draw attention to narratives.'),
(210, 'T0127.001', 'TA10', 'Conduct Physical Violence',       'Directly conduct physical violence to achieve campaign goals.'),
(211, 'T0127.002', 'TA10', 'Encourage Physical Violence',     'Encourage others to engage in physical violence to achieve campaign goals.'),
-- TA11: Persist in Information Env.
(212, 'T0059',     'TA11', 'Play the Long Game',               'Plan messaging and allow it to grow organically over years, or develop seemingly disconnected narratives that eventually combine.'),
(213, 'T0060',     'TA11', 'Continue to Amplify',              'Continue narrative or message amplification after the main incident work has finished.'),
(214, 'T0128',     'TA11', 'Conceal Information Assets',       'Conceal the identity or provenance of campaign information assets to avoid takedown and attribution.'),
(215, 'T0128.001', 'TA11', 'Use Pseudonyms',                   'Use fake names to mask the identity of operational accounts or publish anonymous content.'),
(216, 'T0128.002', 'TA11', 'Conceal Network Identity',         'Hide the existence of an influence operation''s network completely.'),
(217, 'T0128.003', 'TA11', 'Distance Reputable Individuals',   'Enlisted individuals actively disengage themselves from operation activities and messaging.'),
(218, 'T0128.004', 'TA11', 'Launder Information Assets',       'Acquire control of previously legitimate assets through sale or exchange to complicate attribution.'),
(219, 'T0128.005', 'TA11', 'Change Names of Information Assets','Change names or brand names of information assets to avoid detection.'),
(220, 'T0129',     'TA11', 'Conceal Operational Activity',     'Conceal the campaign''s operational activity to avoid takedown and attribution.'),
(221, 'T0129.001', 'TA11', 'Hide Network Identity',            'Aim to hide the existence of the network completely.'),
(222, 'T0129.002', 'TA11', 'Generate Unrelated Content',       'Mix operation content with legitimate or unrelated content to disguise operational objectives.'),
(223, 'T0129.003', 'TA11', 'Break Association with Content',   'Actively separate from own content by unfollowing, unliking, or removing attribution.'),
(224, 'T0129.004', 'TA11', 'Delete URLs',                      'Completely remove website registration to complicate attribution.'),
(225, 'T0129.005', 'TA11', 'Coordinate on Encrypted Networks', 'Coordinate on encrypted/closed networks.'),
(226, 'T0129.006', 'TA11', 'Deny Involvement',                 'Without smoking gun proof, the incident creator can or will deny involvement.'),
(227, 'T0129.007', 'TA11', 'Delete Accounts/Activity',         'Remove online social media assets including accounts, posts, likes, comments to complicate attribution.'),
(228, 'T0129.009', 'TA11', 'Remove Post Origins',              'Eliminate evidence indicating the initial source of operation content.'),
(229, 'T0129.010', 'TA11', 'Misattribute Activity',            'Incorrectly attribute operation activity — mimic another state to frame them for negative behaviour.'),
(230, 'T0130',     'TA11', 'Conceal Infrastructure',           'Conceal the campaign''s infrastructure to avoid takedown and attribution.'),
(231, 'T0130.001', 'TA11', 'Conceal Sponsorship',              'Mislead or obscure the identity of the hidden sponsor behind an operation.'),
(232, 'T0130.002', 'TA11', 'Utilise Bulletproof Hosting',      'Hosting services that allow considerable leniency for suspicious, illegal, or disruptive activities.'),
(233, 'T0130.003', 'TA11', 'Use Shell Organisations',          'Use shell organisations to conceal sponsorship.'),
(234, 'T0130.004', 'TA11', 'Use Cryptocurrency',               'Use cryptocurrency to conceal sponsorship (Bitcoin, Monero, Ethereum).'),
(235, 'T0130.005', 'TA11', 'Obfuscate Payment',                'Obfuscate payment to conceal sponsorship.'),
(236, 'T0131',     'TA11', 'Exploit TOS/Content Moderation',   'Exploiting weaknesses in platforms'' terms of service and content moderation policies to avoid takedowns.'),
(237, 'T0131.001', 'TA11', 'Legacy Web Content',               'Make incident content visible for a long time by exploiting platform terms of service.'),
(238, 'T0131.002', 'TA11', 'Post Borderline Content',          'Post borderline content that skirts platform moderation thresholds.'),
-- TA17: Maximise Exposure
(239, 'T0039',     'TA17', 'Bait Influencer',                     'Trick influencers (celebrities, journalists, local leaders) into amplifying campaign content to access their audience.'),
(240, 'T0049',     'TA17', 'Flood Information Space',             'Flood sources of information with a high volume of inauthentic content to control conversations and drown out opposition.'),
(241, 'T0049.001', 'TA17', 'Trolls Amplify and Manipulate',      'Use trolls to amplify narratives — fake profiles operating to support individuals/narratives across political spectrum.'),
(242, 'T0049.002', 'TA17', 'Flood Existing Hashtag',             'Post content unrelated to the hashtag, ruining functionality, or flood with campaign content.'),
(243, 'T0049.003', 'TA17', 'Bots Amplify via Auto-Forwarding',   'Use automated bots to amplify content above algorithm thresholds without dedicating human resources.'),
(244, 'T0049.004', 'TA17', 'Utilise Spamoflauge',                'Disguise spam messages as legitimate to fool keyword-based email spam filters.'),
(245, 'T0049.005', 'TA17', 'Conduct Swarming',                   'Coordinated use of accounts to overwhelm the information space around a specific event or actor.'),
(246, 'T0049.006', 'TA17', 'Conduct Keyword Squatting',          'Create online content around specific SEO terms to overwhelm search results.'),
(247, 'T0049.007', 'TA17', 'Inauthentic Sites Amplify Narratives','Inauthentic sites cross-post stories and amplify narratives, often without masthead, bylines or attribution.'),
(248, 'T0049.008', 'TA17', 'Generate Information Pollution',     'Flood an information source with inauthentic content to make it harder to find legitimate information.'),
(249, 'T0118',     'TA17', 'Amplify Existing Narrative',         'Amplify existing narratives that align with operation narratives to support objectives.'),
(250, 'T0119',     'TA17', 'Cross-Posting',                      'Post the same message to multiple platforms or accounts to increase content exposure.'),
(251, 'T0119.001', 'TA17', 'Post across Groups',                 'Post content across groups to spread narratives to new communities.'),
(252, 'T0119.002', 'TA17', 'Post across Platforms',              'Post content across platforms to spread narratives and remove opposition context.'),
(253, 'T0119.003', 'TA17', 'Post across Disciplines',            'Post content across different fields of interest.'),
(254, 'T0120',     'TA17', 'Incentivize Sharing',                'Encourage users to share content themselves, reducing the need for the operation to post its own content.'),
(255, 'T0120.001', 'TA17', 'Use Affiliate Marketing Programmes', 'Use affiliate marketing programmes to incentivise sharing.'),
(256, 'T0120.002', 'TA17', 'Use Contests and Prizes',            'Use contests and prizes to incentivise sharing.'),
(257, 'T0121',     'TA17', 'Manipulate Platform Algorithm',      'Conduct activity on a platform that intentionally targets its underlying algorithm to increase content exposure.'),
(258, 'T0121.001', 'TA17', 'Bypass Content Blocking',            'Circumvent network security measures to proliferate content on restricted areas of the internet.'),
(259, 'T0122',     'TA17', 'Direct Users to Alt. Platforms',     'Encourage users to move from the initial platform to alternate information channels.'),
-- TA18: Drive Online Harms
(260, 'T0047',     'TA18', 'Censor Social Media (Political)',      'Use political influence or power of state to stop critical social media comments.'),
(261, 'T0048',     'TA18', 'Harass',                               'Use intimidation techniques, including cyberbullying and doxing, to discourage opponents from voicing dissent.'),
(262, 'T0048.001', 'TA18', 'Boycott/"Cancel" Opponents',           'Exploit cancel culture by emphasising an adversary''s problematic behaviour and presenting own content as alternative.'),
(263, 'T0048.002', 'TA18', 'Harass Based on Identities',           'Harassment targeting social identities like gender, sexuality, race, ethnicity, religion, nationality.'),
(264, 'T0048.003', 'TA18', 'Threaten to Dox',                      'Threaten to publicly release private information about an individual.'),
(265, 'T0048.004', 'TA18', 'Dox',                                  'Publicly release private information including names, addresses, employment, family members.'),
(266, 'T0123',     'TA18', 'Control Info Env. via Cyber Ops',      'Use cyber tools and techniques to alter the trajectory of content in the information space.'),
(267, 'T0123.001', 'TA18', 'Delete Opposing Content',              'Remove content that conflicts with operational narratives from selected platforms.'),
(268, 'T0123.002', 'TA18', 'Block Content',                        'Actions taken to restrict internet access or render certain areas of the internet inaccessible.'),
(269, 'T0123.003', 'TA18', 'Destroy Info Generation Capabilities', 'Limit, degrade, or incapacitate an actor''s ability to generate conflicting information.'),
(270, 'T0123.004', 'TA18', 'Conduct Server Redirect',              'Automatically forward a user from one URL to another without their knowledge.'),
(271, 'T0124',     'TA18', 'Suppress Opposition',                  'Exploit platform content moderation tools to report non-violative content for takedown.'),
(272, 'T0124.001', 'TA18', 'Report Non-Violative Content',         'Report opposing content using copyright regulations to trigger removal of non-violating content.'),
(273, 'T0124.002', 'TA18', 'Goad People into Harmful Action',      'Goad people into actions that violate terms of service, leading to account or content takedown.'),
(274, 'T0124.003', 'TA18', 'Exploit Platform TOS/Moderation',      'Exploit platform terms of service and content moderation systems.'),
(275, 'T0125',     'TA18', 'Platform Filtering',                   'Decontextualization of information as claims cross platforms.'),
-- TA12: Assess Effectiveness
(276, 'T0132',     'TA12', 'Measure Performance',                   '"Are the actions being executed as planned?" — Metric used to determine the accomplishment of actions.'),
(277, 'T0132.001', 'TA12', 'People Focused',                        'Measure the performance of individuals in achieving campaign goals.'),
(278, 'T0132.002', 'TA12', 'Content Focused',                       'Measure the performance of campaign content.'),
(279, 'T0132.003', 'TA12', 'View Focused',                          'Measure performance through view metrics.'),
(280, 'T0133',     'TA12', 'Measure Effectiveness',                 '"Are we on track to achieve the intended new system state within the planned timescale?"'),
(281, 'T0133.001', 'TA12', 'Behaviour Changes',                     'Monitor and evaluate behaviour changes from misinformation incidents.'),
(282, 'T0133.002', 'TA12', 'Content',                               'Measure system state with respect to the effectiveness of campaign content.'),
(283, 'T0133.003', 'TA12', 'Awareness',                             'Measure system state with respect to the effectiveness of influencing awareness.'),
(284, 'T0133.004', 'TA12', 'Knowledge',                             'Measure system state with respect to the effectiveness of influencing knowledge.'),
(285, 'T0133.005', 'TA12', 'Action/Attitude',                       'Measure system state with respect to the effectiveness of influencing action/attitude.'),
(286, 'T0134',     'TA12', 'Measure Effectiveness Indicators (KPIs)','Ensuring Key Performance Indicators are identified and tracked to measure performance during and after campaigns.'),
(287, 'T0134.001', 'TA12', 'Message Reach',                         'Monitor and evaluate message reach in misinformation incidents.'),
(288, 'T0134.002', 'TA12', 'Social Media Engagement',               'Monitor and evaluate social media engagement in misinformation incidents.');

-- ============================================================
-- TABLE: task
-- framework_id = 'FW01' for all Red Framework tasks
-- ============================================================
DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `disarm_id` VARCHAR(10) NOT NULL,
  `tactic_id` VARCHAR(10) NOT NULL,
  `framework_id` VARCHAR(10) NOT NULL,
  `name` LONGTEXT NOT NULL,
  `summary` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `task` (`id`, `disarm_id`, `tactic_id`, `framework_id`, `name`, `summary`) VALUES
-- TA01: Plan Strategy
( 1, 'TK0001', 'TA01', 'FW01', 'Goal setting',                             'Set the goals for this incident.'),
( 2, 'TK0002', 'TA01', 'FW01', 'Population research / audience analysis',  'Research intended audience. Includes audience segmentation, hot-button issues etc.'),
( 3, 'TK0003', 'TA01', 'FW01', 'Campaign design (objective design)',        'Design the campaign(s) needed to meet the incident goals.'),
( 4, 'TK0031', 'TA01', 'FW01', 'OPSEC for TA01',                           'OPSEC activities for Plan Strategy.'),
-- TA02: Plan Objectives
( 5, 'TK0004', 'TA02', 'FW01', 'Identify target subgroups',                'Identify groups that can best be used to meet incident goals.'),
( 6, 'TK0005', 'TA02', 'FW01', 'Analyse subgroups',                        'Analyse identified subgroups.'),
( 7, 'TK0006', 'TA02', 'FW01', 'Create master narratives',                 'Create master narratives for the campaign.'),
( 8, 'TK0007', 'TA02', 'FW01', 'Decide on techniques (4Ds etc)',           'Decide on techniques such as Dismiss, Distort, Distract, Dismay, Divide.'),
( 9, 'TK0008', 'TA02', 'FW01', 'Create subnarratives',                     'Create subnarratives supporting master narratives.'),
(10, 'TK0009', 'TA02', 'FW01', '4chan/8chan coordinating content',          'Coordinate content through 4chan/8chan channels.'),
(11, 'TK0032', 'TA02', 'FW01', 'OPSEC for TA02',                           'OPSEC activities for Plan Objectives.'),
-- TA15: Establish Assets
(12, 'TK0010', 'TA15', 'FW01', 'Present Persona',                          'Present the persona for use in the campaign.'),
(13, 'TK0011', 'TA15', 'FW01', 'Recruit contractors',                      'Recruit contractors to support the campaign.'),
(14, 'TK0012', 'TA15', 'FW01', 'Recruit partisans',                        'Recruit ideologically-aligned individuals.'),
(15, 'TK0013', 'TA15', 'FW01', 'Find influencers',                         'Find influencers to amplify the campaign.'),
(16, 'TK0033', 'TA15', 'FW01', 'OPSEC for TA15',                           'OPSEC activities for Establish Assets.'),
(17, 'TK0014', 'TA15', 'FW01', 'Network building',                         'Build the network needed for the campaign.'),
(18, 'TK0015', 'TA15', 'FW01', 'Network infiltration',                     'Infiltrate existing networks.'),
(19, 'TK0016', 'TA15', 'FW01', 'Identify targets in networks',             'Identify susceptible audience members in networks.'),
(20, 'TK0034', 'TA15', 'FW01', 'OPSEC for TA15 (II)',                      'Additional OPSEC activities for Establish Assets.'),
-- TA05: Microtarget
(21, 'TK0035', 'TA05', 'FW01', 'OPSEC for TA05',                           'OPSEC activities for Microtarget.'),
-- TA06: Develop Content
(22, 'TK0017', 'TA06', 'FW01', 'Content creation',                         'Create content for the campaign.'),
(23, 'TK0018', 'TA06', 'FW01', 'Content appropriation',                    'Appropriate or repurpose existing content.'),
(24, 'TK0036', 'TA06', 'FW01', 'OPSEC for TA06',                           'OPSEC activities for Develop Content.'),
-- TA07: Select Channels & Affordances
(25, 'TK0037', 'TA07', 'FW01', 'OPSEC for TA07',                           'OPSEC activities for Select Channels.'),
-- TA08: Conduct Pump Priming
(26, 'TK0019', 'TA08', 'FW01', 'Anchor trust / credibility',               'Establish trust and credibility before broader content release.'),
(27, 'TK0020', 'TA08', 'FW01', 'Insert themes',                            'Insert campaign themes during pump priming.'),
(28, 'TK0038', 'TA08', 'FW01', 'OPSEC for TA08',                           'OPSEC activities for Conduct Pump Priming.'),
-- TA09: Deliver Content
(29, 'TK0021', 'TA09', 'FW01', 'Deamplification (suppression, censoring)', 'Actions to deamplify opposition content.'),
(30, 'TK0022', 'TA09', 'FW01', 'Amplification',                            'Actions to amplify campaign content.'),
(31, 'TK0039', 'TA09', 'FW01', 'OPSEC for TA09',                           'OPSEC activities for Deliver Content.'),
-- TA10: Drive Offline Activity
(32, 'TK0040', 'TA10', 'FW01', 'OPSEC for TA10',                           'OPSEC activities for Drive Offline Activity.'),
-- TA11: Persist in Information Env.
(33, 'TK0023', 'TA11', 'FW01', 'Retention',                                'Retain audience engagement with the campaign.'),
(34, 'TK0024', 'TA11', 'FW01', 'Customer relationship',                    'Manage relationships with campaign audience members.'),
(35, 'TK0025', 'TA11', 'FW01', 'Advocacy / zealotry',                      'Build advocacy and zealotry around campaign narratives.'),
(36, 'TK0026', 'TA11', 'FW01', 'Conversion',                               'Convert audience members into active campaign supporters.'),
(37, 'TK0027', 'TA11', 'FW01', 'Keep recruiting/prospecting',              'Continue recruiting and prospecting new supporters.'),
(38, 'TK0041', 'TA11', 'FW01', 'OPSEC for TA11',                           'OPSEC activities for Persist in the Information Environment.'),
-- TA12: Assess Effectiveness
(39, 'TK0028', 'TA12', 'FW01', 'Evaluation',                               'Evaluate the effectiveness of the campaign.'),
(40, 'TK0029', 'TA12', 'FW01', 'Post-mortem',                              'Conduct a post-mortem analysis of the campaign.'),
(41, 'TK0030', 'TA12', 'FW01', 'After-action analysis',                    'Perform after-action analysis.'),
(42, 'TK0042', 'TA12', 'FW01', 'OPSEC for TA12',                           'OPSEC activities for Assess Effectiveness.');

SET foreign_key_checks = 1;
