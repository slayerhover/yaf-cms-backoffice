DROP TABLE IF EXISTS go_admin;
CREATE TABLE go_admin (
   id int(8) unsigned NOT NULL auto_increment,
   username varchar(32) NOT NULL,
   roles_id int(11) NOT NULL,
   password varchar(32),
   loginedtimes int(8),
   lasttime int(11),
   PRIMARY KEY (id),
   KEY UserName (username)
);

INSERT INTO go_admin VALUES('1', 'admin', '1', '132d4bacc660c537f2049694260a2751', '25', '1494152676');
INSERT INTO go_admin VALUES('2', 'user', '2', '132d4bacc660c537f2049694260a2751', '6', '1461122370');
INSERT INTO go_admin VALUES('3', 'client', '4', '132d4bacc660c537f2049694260a2751', '0', NULL);

DROP TABLE IF EXISTS go_categories;
CREATE TABLE go_categories (
   id int(8) NOT NULL auto_increment,
   title varchar(64) NOT NULL,
   sortorder int(8),
   up int(8) NOT NULL,
   is_active tinyint(1) DEFAULT '1' NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_categories VALUES('1', '饮品', '500', '0', '1');
INSERT INTO go_categories VALUES('2', '乐器', '0', '0', '1');
INSERT INTO go_categories VALUES('4', '云朵', '100', '0', '1');
INSERT INTO go_categories VALUES('5', '白酒', '0', '0', '1');
INSERT INTO go_categories VALUES('6', '黑盒', '0', '2', '1');

DROP TABLE IF EXISTS go_coinfo;
CREATE TABLE go_coinfo (
   id int(11) NOT NULL auto_increment,
   co_name varchar(128) NOT NULL,
   co_address varchar(256) NOT NULL,
   co_postcode varchar(16) NOT NULL,
   co_lines varchar(64),
   co_phone varchar(64) NOT NULL,
   co_shouhou varchar(64),
   co_fax varchar(64),
   co_contacter varchar(64),
   co_email varchar(128),
   sitecode text,
   sitecodestatus tinyint(1) DEFAULT '1' NOT NULL,
   title varchar(256),
   keywords varchar(256),
   description varchar(512),
   PRIMARY KEY (id)
);

INSERT INTO go_coinfo VALUES('1', 'YAF通用网站管理系统', '河南郑州数码大厦10楼', '450000', '152,35,60', '18918567900', '0371-65878381', '0371-65878381', 'slayer.hover', '0371-65878381', 'PHNjcmlwdCB0eXBlPSd0ZXh0L2phdmFzY3JpcHQnPgokKGZ1bmN0aW9uICgpIHsKICAgIHNldFRpbWVvdXQoZnVuY3Rpb24gKCkgeyBpbmNyZW1lbnRWaWV3Q291bnQoY2JfZW50cnlJZCk7IH0sIDUwKTsKICAgIGRlbGl2ZXJBZFQyKCk7CiAgICBkZWxpdmVyQWRDMSgpOwogICAgZGVsaXZlckFkQzIoKTsgICAgCiAgICBsb2FkTmV3c0FuZEtiKCk7CiAgICBsb2FkQmxvZ1NpZ25hdHVyZSgpOwogICAgTG9hZFBvc3RJbmZvQmxvY2soY2JfYmxvZ0lkLCBjYl9lbnRyeUlkLCBjYl9ibG9nQXBwLCBjYl9ibG9nVXNlckd1aWQpOwogICAgR2V0UHJldk5leHRQb3N0KGNiX2VudHJ5SWQsIGNiX2Jsb2dJZCwgY2JfZW50cnlDcmVhdGVkRGF0ZSk7CiAgICBsb2FkT3B0VW5kZXJQb3N0KCk7CiAgICBHZXRIaXN0b3J5VG9kYXkoY2JfYmxvZ0lkLCBjYl9ibG9nQXBwLCBjYl9lbnRyeUNyZWF0ZWREYXRlKTsgICAgCn0pOwo8L3NjcmlwdD4=', '0', 'YAF CMS系统', 'YAF CMS系统', 'YAF CMS系统');

DROP TABLE IF EXISTS go_friendlink;
CREATE TABLE go_friendlink (
   id int(8) NOT NULL auto_increment,
   title varchar(32) NOT NULL,
   links varchar(64),
   url varchar(128),
   sortorder int(8),
   status tinyint(1) DEFAULT '1' NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_friendlink VALUES('3', 'Chem Catch', 'http://www.chemcatch.com', '/uploads/Home/home-t1461576590.jpg', '10', '1');
INSERT INTO go_friendlink VALUES('5', 'Synaptic Systems', 'http://www.sysy.com', '/uploads/Home/home-t1462262538_thum.jpg', '30', '1');
INSERT INTO go_friendlink VALUES('10', 'YAF CMS系统', 'http://www.yaf.com', '/uploads/Home/home-t1462262427_thum.jpg', '60', '1');

DROP TABLE IF EXISTS go_guestbook;
CREATE TABLE go_guestbook (
   id int(11) NOT NULL auto_increment,
   nickname varchar(64),
   phone varchar(16),
   email varchar(64),
   addtime int(11) NOT NULL,
   content varchar(512),
   reply varchar(512),
   ip varchar(24) NOT NULL,
   status tinyint(1) NOT NULL,
   PRIMARY KEY (id)
);


DROP TABLE IF EXISTS go_images;
CREATE TABLE go_images (
   id int(8) NOT NULL auto_increment,
   url varchar(128) NOT NULL,
   title varchar(64) NOT NULL,
   links varchar(64) NOT NULL,
   status tinyint(1) DEFAULT '1' NOT NULL,
   sortorder int(8) NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_images VALUES('13', '/uploads/Home/home-t1461320749.jpg', 'YAF CMS系统', '#', '1', '10');
INSERT INTO go_images VALUES('14', '/uploads/Home/home-t1461555013.jpg', 'YAF CMS系统', '#', '1', '20');

DROP TABLE IF EXISTS go_members;
CREATE TABLE go_members (
   id int(11) NOT NULL,
   username char(15),
   admin tinyint(1),
   PRIMARY KEY (id)
);

INSERT INTO go_members VALUES('1', 'slayerhover', '0');

DROP TABLE IF EXISTS go_menus;
CREATE TABLE go_menus (
   id int(8) NOT NULL auto_increment,
   title varchar(16) NOT NULL,
   links varchar(64) NOT NULL,
   up int(8) NOT NULL,
   sortorder int(8) NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_menus VALUES('2', '公司产品', '/index/listproducts.html', '0', '500');
INSERT INTO go_menus VALUES('3', '新闻资讯', '/index/listnews.html', '0', '300');
INSERT INTO go_menus VALUES('4', '关于我们', '#', '0', '400');
INSERT INTO go_menus VALUES('6', '售后服务', '/index/viewpages.html?id=10', '0', '200');
INSERT INTO go_menus VALUES('9', '云朵', '/index/listproducts.html?id=4', '2', '10');
INSERT INTO go_menus VALUES('11', '联系我们', '/index/viewpages.html?id=4', '0', '40');
INSERT INTO go_menus VALUES('13', '公司动态', '/index/listnews.html?id=1', '3', '20');
INSERT INTO go_menus VALUES('15', '企业简介', '/index/viewpages.html?id=1', '4', '10');
INSERT INTO go_menus VALUES('21', '在线留言', '/index/contactus.html', '0', '100');
INSERT INTO go_menus VALUES('22', '行业新闻', '/index/listnews.html?id=9', '3', '0');
INSERT INTO go_menus VALUES('23', '团队介绍', '/index/viewpages.html?id=9', '4', '0');
INSERT INTO go_menus VALUES('24', '黑盒', '/index/listproducts.html?id=6', '2', '0');
INSERT INTO go_menus VALUES('25', '乐器', '/index/listproducts.html?id=2', '2', '0');
INSERT INTO go_menus VALUES('26', '饮品', '/index/listproducts.html?id=1', '2', '0');

DROP TABLE IF EXISTS go_news;
CREATE TABLE go_news (
   id mediumint(7) unsigned NOT NULL auto_increment,
   title varchar(128) NOT NULL,
   newsclass_id int(11) NOT NULL,
   hits mediumint(7) NOT NULL,
   author varchar(30),
   copyfrom varchar(100),
   copyfromurl varchar(150),
   titlecolor varchar(15),
   fonttype tinyint(1),
   logo varchar(255),
   status tinyint(1) DEFAULT '1' NOT NULL,
   recommend tinyint(1) NOT NULL,
   keywords varchar(255) NOT NULL,
   addtime int(11) NOT NULL,
   edittime int(11) NOT NULL,
   sortorder int(11) NOT NULL,
   PRIMARY KEY (id),
   KEY hits (hits,status)
);

INSERT INTO go_news VALUES('2', '服务双周圆满落幕。four year.', '15', '111', 'slayer.hover', 'netwall', 'http://www.netwall.com', '', '0', '/uploads/News/201604/news-t1461835045.gif', '1', '1', '周圆, 落幕', '0', '1462355578', '10');
INSERT INTO go_news VALUES('3', '郑州日产NV200车主使用汇报', '1', '0', 'hover', '中部网', 'http://www.zhongbuauto.com', '', '0', 'News-t1329381845.jpg', '1', '1', '中部汽车,促销节', '0', '1462356379', '50');
INSERT INTO go_news VALUES('4', 'SUV城市新体验汉兰达舒适百分之一千', '9', '0', '', '', '', '', '0', '/uploads/News/201605/news-t1462355169_thum.jpg', '1', '1', '汉兰达 雅力士 FJ酷路泽 凯美瑞 埃尔法', '0', '1462355169', '40');
INSERT INTO go_news VALUES('7', '最新调研：电动车并不环保中国或误入歧途？', '9', '0', 'wind', '中国汽车质量网', 'http://www.12365auto.com/zlts/', NULL, NULL, 'News-t1329643939.jpg', '1', '0', '最新调研,电动车,环保,误入歧途', '0', '1462352432', '0');
INSERT INTO go_news VALUES('8', '新奥拓K10B—“十佳”微轿发动机', '1', '0', 'wind', '中国汽车质量网', 'http://www.12365auto.com/zlts/', NULL, NULL, 'News-t1329643988.jpg', '1', '1', '最新调研,电动车,环保,误入歧途', '0', '1462353458', '210');
INSERT INTO go_news VALUES('11', '男孩未能10天内凑够10万彩礼 被女友家人丢丢在高速', '1', '0', 'authors', 'sources', 'urls', NULL, NULL, 'Upload\\News\\Home-t1459064327.jpg', '1', '0', '企业愿景', '0', '0', '20');
INSERT INTO go_news VALUES('12', '不出村就可取钱啦，农汇通助农取款设备进驻中牟', '10', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '1462264083', '0');
INSERT INTO go_news VALUES('13', '农汇通助农取款设备进驻河南临颍', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '1462264058', '60');
INSERT INTO go_news VALUES('14', '人民币贬值4.66% 基金：对股市长期影响有限', '1', '0', '', '', '', NULL, NULL, '/uploads/News/201604/news-t1461837333.jpg', '1', '0', '', '0', '1461837333', '0');
INSERT INTO go_news VALUES('15', '关于调整个人借记卡ATM跨行转账汇款业务收费标准的公示', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '220');
INSERT INTO go_news VALUES('16', '关于8月16日系统升级期间暂停中银开放平台相关服务的公 ', '10', '0', '', '', '', NULL, NULL, NULL, '1', '1', '', '0', '1462410118', '110');
INSERT INTO go_news VALUES('18', '金融创新助力“一带一路”战略 广西利用沿边区位优势推', '10', '0', '', '', '', NULL, NULL, NULL, '1', '1', '', '0', '1462353468', '80');
INSERT INTO go_news VALUES('19', '证监会主席助理张育军涉嫌严重违纪接受组织调查', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '0');
INSERT INTO go_news VALUES('20', '决策层一周内三提抓改革 释放政策红利稳增长 ', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '10');
INSERT INTO go_news VALUES('21', '决策层一周内三提抓改革 释放政策红利稳增长A ', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '0');
INSERT INTO go_news VALUES('22', '决策层一周内三提抓改革 释放政策红利稳增长B', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '0');
INSERT INTO go_news VALUES('30', '【一带一路 共建繁荣】', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '0');
INSERT INTO go_news VALUES('31', '中国银行全球公司金融', '15', '0', '', '', '', NULL, NULL, '/uploads/News/201605/news-t1462356499_thum.jpg', '1', '1', '', '0', '1462356503', '30');
INSERT INTO go_news VALUES('32', ' 20150812—人民币贬值成', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '0');
INSERT INTO go_news VALUES('33', '.债市参考2015年08月13日', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '70');
INSERT INTO go_news VALUES('34', '.汇市日评2015年08月13日', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '0');
INSERT INTO go_news VALUES('35', '汇市观潮2015年08月13日', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '0');
INSERT INTO go_news VALUES('36', '黄金观潮2015年08月13日', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '200');
INSERT INTO go_news VALUES('37', '黄金观潮2015年08月14日', '1', '0', '', '', '', NULL, NULL, NULL, '1', '0', '', '0', '0', '100');
INSERT INTO go_news VALUES('38', '一篇新的图片新闻', '1', '0', 'slayer.hover', 'netwall', 'http://www.netwall.com', NULL, NULL, NULL, '1', '0', '闪电', '0', '1461899207', '0');
INSERT INTO go_news VALUES('39', '第二篇图片新闻', '1', '0', 'slayer.hover', 'netwall', 'http://www.netwall.com', NULL, NULL, '/uploads/News/201604/news-t1461899119_thum.jpg', '1', '0', '闪电', '0', '1462261109', '230');
INSERT INTO go_news VALUES('40', '海边小桥', '9', '0', 'slayer.hover', 'source', 'http://www.netwall.com', NULL, NULL, '/uploads/News/201702/news-t1486890894.jpg', '1', '1', '小乔初嫁了', '1462261361', '1486890894', '500');

DROP TABLE IF EXISTS go_newsclass;
CREATE TABLE go_newsclass (
   id int(11) NOT NULL auto_increment,
   title varchar(64) NOT NULL,
   up int(8),
   sortorder int(8) NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_newsclass VALUES('1', '新闻公告', '0', '10');
INSERT INTO go_newsclass VALUES('9', '图片新闻', '0', '0');
INSERT INTO go_newsclass VALUES('10', '最新公告', '0', '0');
INSERT INTO go_newsclass VALUES('15', '新闻中心', '0', '0');

DROP TABLE IF EXISTS go_newscontent;
CREATE TABLE go_newscontent (
   id mediumint(7) NOT NULL,
   content mediumtext NOT NULL
);

INSERT INTO go_newscontent VALUES('2', '<img src=\"/uploads/News/201604/news-t1461836871.gif\" alt=\"\" /><br /><p>受整体经济环境的影响，与往年相比，2008年车市表现较为平淡，增长速度放缓。与此同时，中高级车经典车型凯美瑞却逆市保持稳健，单品销量傲视群雄。据交管部门统计，广汽丰田凯美瑞全年上牌量达到14.7万台，继2007年首次夺冠之后，再度夺得中高级车全年上牌量冠军。</p><p>数据显示，凯美瑞12月份单月上牌量达到16782台，以领先第二名2680台的优势夺得当月中高级车上牌量冠军。12月份的强势表现进一步扩大了凯美瑞在全年上牌量榜上的领先优势，使凯美瑞以146872台的成绩将中高级车2008年度上牌量冠军稳稳地揽入囊中。纵观2008年，凯美瑞在新车光环褪尽的情况下，不仅成功抵御了车市寒流的冲击，更成功地从众多竞品更新换代的围攻中成功突围，一直以强劲势头领跑中高级车市。</p><p>行业分析师介绍，批发量和上牌量是汽车业内常用的统计数据，其中上牌量能够更准确地反映销售终端的实际零售情况，而且从批发量和上牌量之间的差额上也能从侧面看出经销商的库存压力。凯美瑞连续两年夺得中高级车全年上牌量冠军，是终端零售上的胜利，具有较高的含金量。</p><p>对于一款上市三年的车型来说，取得如此成绩让业界无不叹服。业内专家认为，凯美瑞上市三年以来销售一直保持稳中有升的态势，说明国内消费者喜欢“追新”的消费习惯正在发生变化，是我国汽车市场更趋成熟的表现。我国车市经过数年高速发展，逐步与国际市场接轨。凯美瑞的持续畅销证明“品牌、品质、服务”——欧美汽车市场的三大竞争要素正在成为我国车市竞争决胜的关键。</p><p align=\"center\"><strong>凯美瑞 品牌、品质笑傲群雄</strong></p><p align=\"left\">广汽丰田骏驰金水路店专业讲师毛海瑞认为，在市场大环境不佳的情况下，凯美瑞本身所具有的强大品牌优势和产品竞争力是保持畅销的首要原因。众所周知，丰田汽车设计完美、工艺精致。凯美瑞这款以“尊贵而不失动感”为设计理念的车型，车身造型尊贵大气，线条典雅流畅，内饰布局错落有致，动静之间皆流露出非凡的尊贵之感。性能上，凯美瑞主打“均衡”，安全性能突出，动力充沛，操控顺畅，燃油经济性出色，而且工艺品质优秀，耐用性上佳，充分满足了中高级车消费者的主流需要。因此凯美瑞在其三十多万车主中形成了极佳的口碑，被誉为中高级车市的“全能冠军”。</p><p align=\"left\">2008年9月，新增VSC车辆稳定性控制系统、TRC牵引力控制系统等实用配置的09款凯美瑞升级上市，并且调整了厂家建议零售价格。与同等级车型相比，09款凯美瑞在安全性、豪华感、性价比方面更具优势，赢得了更多潜在客户。实力雄厚且不断提升的产品力使凯美瑞上市两年后，在遭遇金融风暴、面临众多竞品的换代的不利市场环境中，仍能以绝佳的成绩笑傲中高级车市。</p><p align=\"center\"><strong>广汽丰田 令最挑剔的客户心悦诚服</strong></p><p align=\"left\">早在凯美瑞上市两周年之际，凯美瑞即以销售30万台的成绩被誉为行业神话；2008年，凯美瑞更以14.7万台的上牌量夺得中高级车冠军。凯美瑞的持续畅销离不开广汽丰田强大的服务支持。“第一台车是销售卖的，第二台车是服务卖的”，广汽丰田骏驰金水路店的销售经理田攀这样评价服务的重要性。</p><p align=\"left\">广汽丰田骏驰金水路店的服务带给消费者的是一种尊重和安心的感觉。顾客从走进销售店那一刻起，甚至是从致电销售店那一刻起，就已经成为广汽丰田的贵宾。各种方式的温馨提醒、便利的预约、热情的接待、无压力的购车环境、人性化的休息区、省时的EM快速保养通道、值得信赖的“一次完修”，以及灿烂的微笑、殷切的问候和回访，都使顾客省时、安心并且油然而生朋友般的信赖感。</p><p align=\"left\">“我们的目标是时时、处处让顾客满意，使顾客成为我们的终身用户”，广汽丰田金水路店副总经理符瑜表示，“我们所有销售人员的心愿就是为全国各地的凯美瑞车主提供同等品质的服务，达到顾客满意度第一。” 为此，广汽丰田骏驰金水路店确立了“Personal &amp; Premium”（贴心尊贵）渠道理念，导入丰田全球领先的e-CRB系统，致力于向顾客提供一对一的尊贵服务，为顾客提供超越期盼的综合价值。</p><p>经过短短两年多时间，广汽丰田打造的“凯美瑞体验”已成为消费者心中“称心满意”的代名词。在不久前举行的“2008中国汽车服务金扳手奖、金手指奖”颁奖典礼上，广汽丰田喜获“年度优秀服务品牌——客户满意度大奖”，说明广汽丰田以“顾客满意度第一”为目标打造的尊贵、贴心服务也获得了业界的认可。</p><p>如果说凯美瑞最初两年的成功是因为源于佳美的品牌美誉，那么在2008年车市低迷的考验下，凯美瑞销量常青、再夺桂冠，则是其品牌、品质、服务全面发力的结果。广汽丰田执行副总经理冯兴亚表示，新车效应总有消退的时候，广汽丰田不仅希望凯美瑞成为最受欢迎的新车，更致力于把凯美瑞打造成为“价值标杆”。为此，广汽丰田打造了以卓越的产品、先进的工厂、创新的渠道构成的“三位一体”的品质保障体系。历经三年的积累和进化，凯美瑞“愈磨砺、愈光芒”，市场表现不降反升，“价值标杆”地位更趋稳固。</p><p align=\"left\">广汽丰田骏驰金水路店 </p><p align=\"left\">销售热线：0371-60979999</p><p align=\"left\">政府采购热线：0371-60975888</p><p align=\"left\">售后服务热线：0371-60976011</p><p align=\"left\">24小时救援热线：0371-60970000</p><p align=\"left\">网址：http://www.junchitoyota.com/ </p><p align=\"left\">地址：郑州市金水路与玉凤路交叉口向南500米</p>');
INSERT INTO go_newscontent VALUES('3', '<span style=\"font-family:宋体;\"><span style=\"font-size:16px;\"><span style=\"font-family:simsun;font-size:14px;\">&nbsp;&nbsp;&nbsp;&nbsp;【中部汽车网 资讯】&nbsp;购车郑州尼桑NV200行驶330公里来报个到，豪华沙滩银加装DVD倒车影像、坐套、美国AC膜、地毯等。全部市区行驶仪表显示8.8升，提车显示5公里加满油54.36升一箱看能行驶多少公里。本人驾龄27年，03年家中购第一台车QQ1.1东安发动机第一批车现已行驶13万公里，今年5月开始筛选7--8坐车最终通过试驾于12月3日&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">购车郑州尼桑NV200。</span></wbr></span><span style=\"font-family:simsun;font-size:14px;\"> </span></span><div style=\"padding-right:0px;padding-left:0px;padding-bottom:0px;margin:0px;word-spacing:0px;font:14px/25px 微软雅黑, 宋体, \'arial narrow\', arial, serif;text-transform:none;color:#000000;text-indent:0px;padding-top:0px;white-space:normal;letter-spacing:normal;background-color:#ffffff;webkit-text-size-adjust:auto;orphans:2;widows:2;webkit-text-stroke-width:0px;\"><span style=\"font-size:14px;\"><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;&nbsp; </span><wbr><span style=\"font-family:simsun;font-size:14px;\">对于1.6排量以后使用会比较经济（油贵啊）毕竟节约能源也是爱国。330公里全是我驾驶动力比我想象要好，静音比别克GL8强（我已开了5年）。行车电脑可以设置超速、怠速、换挡、机油、轮胎等等报警确实很爽。转向准确、刹车反应灵敏减震不错。昨天我把此车开到三面是墙（约有10米）的地方终于听到离合器分力轴承沙沙声音十分微弱，踩下离合随即消失，应不是什么毛病。挂档一定慢抬离合器不会出响声但4档有齿轮齿合不畅的感觉。也比较好操控，三元催化不是一般好，轮胎确实小，空调不知给力不（不行每人发一把扇子）最大毛病价格如果国产化可能价格会下来一些。暖风很给力一档都说热总之能用到都有能省的都省。</span></wbr></wbr></span></div><div style=\"padding-right:0px;padding-left:0px;padding-bottom:0px;margin:0px;word-spacing:0px;font:14px/25px 微软雅黑, 宋体, \'arial narrow\', arial, serif;text-transform:none;color:#000000;text-indent:0px;padding-top:0px;white-space:normal;letter-spacing:normal;background-color:#ffffff;webkit-text-size-adjust:auto;orphans:2;widows:2;webkit-text-stroke-width:0px;\"><span style=\"font-size:14px;\"><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;&nbsp; </span><wbr><span style=\"font-family:simsun;font-size:14px;\">在此感谢洛阳天璐汽车4S张凯不焉其烦试车解答。以上是我驾车感受向毛主席保证真实。49岁的人了打字慢有错别字请谅解。</span></wbr></wbr></span></div><div style=\"padding-right:0px;padding-left:0px;padding-bottom:0px;margin:0px;word-spacing:0px;font:14px/25px 微软雅黑, 宋体, \'arial narrow\', arial, serif;text-transform:none;color:#000000;text-indent:0px;padding-top:0px;white-space:normal;letter-spacing:normal;background-color:#ffffff;webkit-text-size-adjust:auto;orphans:2;widows:2;webkit-text-stroke-width:0px;\"><span style=\"font-size:14px;\"><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\"> </span><wbr><wbr><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></span><a href=\"http://photo.blog.sina.com.cn/showpic.html#blogid=8503e0070100wtf9&amp;url=http://s9.sinaimg.cn/orignal/8503e007tb4f6b8fbb1a8\" target=\"_blank\"><span style=\"font-size:18px;\"><span style=\"font-size:14px;\"><span style=\"font-family:simsun;font-size:14px;\"> </span></span></span></a><br /><p style=\"text-indent:2em;\"><span style=\"font-size:24px;\"><strong><span style=\"font-size:14px;\"><span style=\"font-family:simsun;font-size:14px;\">郑州日产，天璐首选&nbsp;</span><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><br /><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><span style=\"font-family:simsun;font-size:14px;\">&nbsp;</span><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><span style=\"font-family:simsun;font-size:14px;\">主营：帅客、NV200、帕拉丁、锐骐皮卡、D22皮卡、奥丁、御轩等全系车型&nbsp;</span><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr><wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></wbr></span></strong></span></p><p style=\"text-indent:2em;\"><span style=\"font-size:24px;color:#ff0000;\"><strong style=\"font-size:14px;\"><span style=\"font-family:simsun;font-size:14px;\">郑州日产洛阳天璐4S店</span></strong></span></p></div>');
INSERT INTO go_newscontent VALUES('4', '<span style=\"font-size:16px;\"><span style=\"font-size:14px;\">【中部汽车网 资讯】&nbsp;十多年前，人们喜欢开着越野车去冒险，寻求刺激；而今，喧闹的都市里，人们选择SUV更多是希望能够在喧哗中获得一份宁静与舒适。于是，以汉兰达为代表的城市型SUV，在保留传统越野车高通过性和开阔视野基础上，兼备轿车的舒适性和操控性，受到越来越多消费者的青睐。</span></span><span style=\"font-size:14px;\"></span><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\">汉兰达出自丰田著名的中型车平台，比肩豪华轿车的高品质底盘技术，以及高技术含量的自动空调系统，为汉兰达的驾乘舒适打下坚实的基础。</span></span></p><p>&nbsp;</p><p align=\"center\"><span style=\"font-size:14px;\"><img src=\"http://www.zhongbuauto.com/attachment/allimg/201012/20101226021219_53934.jpg\" alt=\"\" border=\"0\" /></span></p><span style=\"font-size:14px;\"></span><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\"><strong style=\"font-size:14px;\">最成熟悬挂：坐得舒服跑得稳<br /></strong>&nbsp;&nbsp;&nbsp;&nbsp;汉兰达高大的车身配备了媲美高级轿车的悬挂系统，采用了前麦弗逊独立悬挂后双连杆式悬挂系统，保证良好直线行驶以及转弯的稳定性，可应付市区和郊区的不良路况，使操控更加从容自如，乘坐更加舒适自在。</span></span></p><span style=\"font-size:14px;\"></span><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp;&nbsp;麦弗逊独立式悬挂是目前技术最为成熟的前悬挂系统，也是城市型SUV的主流配置。它结构简单紧凑，占用空间小，可有效扩大车内乘坐空间，舒适性相当好。</span></span></p><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp; 只是麦弗逊独立式悬挂横向稳定性差，抗冲击能力差。针对这个缺点，汉兰达的开发工程师对悬臂、弹簧和减震器等部件进行了全方位的改良，大幅提升了抗冲击能力。例如平衡杆装置就创新性地应用了最佳的几何学角度进行设计，可以充分过滤地面颠簸，时刻保持行驶稳定性。</span></span></p><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp;&nbsp;减震器总成是汉兰达对麦弗逊式独立悬挂改良的最大亮点，由多片式线性控制阀和反弹弹簧组成。</span><br /><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp;&nbsp;汉兰达在双连杆式独立后悬挂系统中参考了高级轿车的设计特点，融入了多连杆式悬架的理念，从容应对各种路况，带来更佳的减震性、高刚性和优异操控性。此外，后悬挂还扩大了轮距，在增加了操纵平稳性的同时，提供了更加宽敞的后排乘坐和行李厢空间。</span></span></p><p>&nbsp;</p><p align=\"center\"><span style=\"font-size:14px;\"><img src=\"http://www.zhongbuauto.com/attachment/allimg/201012/20101226021243_36913.jpg\" alt=\"\" border=\"0\" /></span></p><span style=\"font-size:14px;\"></span><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\"><strong style=\"font-size:14px;\">神经网络控制自动空调系统：高科技打造最适宜温度<br /></strong>&nbsp;&nbsp;&nbsp;&nbsp;如果说，成熟的悬挂系统为汉兰达的舒适性奠定基础，那么，带神经网络控制的自动空调系统等高科技配置，则为驾驶者和乘坐者提供最大化的舒心、惬意，如沐春风。</span></span></p><span style=\"font-size:14px;\"></span><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp;&nbsp;汽车内部温度是舒适性的重要指标。汉兰达采用了3区独立控温空调系统，由车内的前后配置温度传感器进行控制，让驾驶席、副驾驶席以及后排空间可以分别按需调节温度。尤其值得一提的是，在右侧后围饰板设置了1个后方腿部出风口，最大程度的满足了后排乘客对温度的需求。</span></span></p><p><br /><span style=\"font-size:14px;\"><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp;&nbsp;不仅如此，汉兰达的自动空调系统中，还配置有神经网络控制功能，这种更高层次的控制技术，能够根据人体所处环境而精确控制温度。这样，乘员只要操作旋钮或按键，设置所需温度及风机转速，以后一切事情都由自动空调控制系统办理了。</span></span></p>');
INSERT INTO go_newscontent VALUES('8', '<p><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp; 要说电动车的好处，充分体现在电动自行车上，因为电动自行车足够轻，只需要一点动力即可驱动，优于其他形式的交通运输工具。</span></p><p><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp; 这项新的调查是否会影响到政府政策并不确定。目前，政府希望中国成为电动车领域的领军力量，已经大力支持开发电力技术。</span></p><p><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp; 中央政府已经提供了一系列电动车鼓励措施，不只包括现金激励，还可免去北京等地购车消费者的排队摇号之苦。</span></p><p><span style=\"font-size:14px;\">&nbsp;&nbsp;&nbsp; 研究调查人员称：“在过去十年中，中国汽车销量超过了1亿，中国车市将成为新能源汽车的最大单一市场 。”而越来越多的电动汽车上路将让中国空气质量越来越差，除非中国政府找到一个更洁净的发电系统。</span></p>');
INSERT INTO go_newscontent VALUES('7', '<p>&nbsp;&nbsp;&nbsp; 2月17日消息 据美国媒体The DETROIT Bureau报道, 日前美国田纳西州大学、明尼苏达州大学以及中国的清华大学进行的一份联合调查研究结果显示：推行电动汽车会污染空气，特别是像北京这样的大城市。该调查称，尽管政府为了减少污染大力推行电池驱动车型，但是实际上却是在将原来的污染源替换成了另外一种。</p><p align=\"center\"><img src=\"http://www.12365auto.com/UploadFiles/2012_02_17_17_02_38.jpg\" border=\"0\" alt=\"\" /></p><p align=\"center\"><span style=\"font-size:12px;\">山西大同煤炭工厂</span></p><p>&nbsp;&nbsp;&nbsp; 在中国多数地方，依旧是煤炭发电，而大多数车辆使用汽油、柴油作为动力能源。该调查研究报告建议，对于大多数中国消费者来说，燃油汽车、或者是传统的混合动力系统要比支持者所说的所谓的“零排放”的车（ZEV）要清洁。</p><p>&nbsp;&nbsp;&nbsp; “绿色能源”的利好在不同地区各不相同。在中国，68%的电力来自于煤炭发电。但是，在其他一些地方，例如三峡地区，有良好的水力发电条件，其回报以及开发潜力更大。</p><p>&nbsp;&nbsp;&nbsp; 这项研究主要调查了中国34个大城市中的电动汽车、自行车以及小型摩托车对环境的影响。这次调查不像其他的调查只关注于排气管排出的气体，同时调查车辆动力来源的污染情况。</p><p>&nbsp;&nbsp;&nbsp; 调查显示，在空气质量不好、烟雾迷绕的北京，电动车的能耗仅相当于一辆9.1升/百公里的汽车，几乎快要达到搭载V6发动机的中级车的油耗以及碳排放。</p><p>&nbsp;&nbsp;&nbsp; 在中国西南的城市成都，洁净电力系统意味着电动车的清洁程度相当于一辆百公里耗油5.6升的汽车，但这还是要比丰田普锐斯（Toyota Prius）等混合动力车型造成的污染大。</p><p>&nbsp;&nbsp;&nbsp; 该调查报告发表在《环境科学技术》（Environmental Science Technology）杂志上，称在中国，从健康角度讲，电动车环保程度与柴油车不相上下，但不如汽油车。</p>');
INSERT INTO go_newscontent VALUES('11', '<p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">近年来，原本盲目攀比之风不知害了多少“恋人”。而在北京打工的湖北省阳新县青年刘某与女朋友的遭遇的不仅是流着泪“望彩兴叹”。3月26日夜晚，刘某原本高高兴兴到周口市太康县“提钱”见女朋友家人，不料因10天内未能凑足10万元彩礼，“老岳”一时气恼，竟连夜开车将他强行送走，丢到50公里外的商周高速公路上扬长而去。孤身在外、焦急不已的刘某幸遇商丘高速交警，在民警的热心救助下，才化险为夷回归北京，但彩礼成了他迈不进婚姻的门槛，留下一时心悸的梦魇。</p><p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">据商丘市公安局交警支队高速大队五中队副中队长邵辉介绍，当日晚上7时47分，他和协警赵浩博巡逻途中接大队指挥中心电话称，有一男子被人甩到商周高速75公里东半幅。</p><p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">他们迅速赶到现场，发现一男青年小伙独身一人在高速公路边走边哭边絮叨。经询问得知，该小伙名叫刘某，今年21岁，系湖北省阳新县陶港镇人，在北京某理发店做理发师。他含泪告诉民警，他的女朋友是周口市太康县人，他俩关系很好，准备结婚，前几天就把婚事给双方家长说了。</p><p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">“老岳”一家知道后，就把女儿带回来太康老家，并要让他10日内准备10万元彩礼到家提亲。可是，刚参加工作两年的刘某，手里根本没多少存款，又不愿太难为家人，这么短的时间他未能凑够10万元钱，遂掂着几万元钱坐车从北京来到太康女朋友家，准备缓缓再说。</p><p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">谁知女朋友父母“认钱不认人”，听说没有凑足彩礼，就强行把女朋友手机关机，也不让他们见面，随后被“老丈人”开车拉到50公里外的商周高速公路75KM附近，撵他下车后弃他而去。刘某下车后发现天已经黑了，也根本不知道啥地方，身边车辆呼啸而过。</p><p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">“高兴而来，败兴而归，让他死的味都有”，刘某说，人生地不熟，他只好报警进行求助。民警了解情况后，耐心劝说刘某，稳定其情绪后，把他送到柘城县一家宾馆，并为他安排好食宿。</p><p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">“遭遇‘彩礼’痛心囧事让我痛不欲生，非常感谢商丘高速交警，是你们让我恢复理智和生活的信心”，27日上午刘某在返回北京的火车上向民警发来感谢短信。</p><p style=\"margin: 0px 0px 29px; padding: 0px; font-size: 16px; line-height: 28px; color: rgb(0, 0, 0); font-family: 宋体, Arial, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: auto; word-spacing: 0px; -webkit-text-stroke-width: 0px; text-indent: 2em; background-color: rgb(255, 255, 255);\">彩礼原本是一种习俗，男提彩礼，女配嫁妆，表示一下诚意。然而，在现实生活中，一些家长盲目攀比，通过索要高额彩礼来获取所谓的面子。殊不知，婚姻不是买卖，儿女不是商品。我们可以保留彩礼这种形式，但是应该掌握好尺度，量力而行，让彩礼符合健康文明和谐简约的风气，切莫盲目攀比，让畸形彩礼葬送了幸福婚姻。对于父母而言，子女找到情投意合的另一半，彼此关怀爱护、拥有幸福美满的家庭才是最好的彩礼。</p>');
INSERT INTO go_newscontent VALUES('12', '不出村就可取钱啦，农汇通助农取款设备进驻中牟');
INSERT INTO go_newscontent VALUES('13', '农汇通助农取款设备进驻河南临颍');
INSERT INTO go_newscontent VALUES('14', '<p>人民币贬值4.66% 基金：对股市长期影响有限</p><p><img src=\"/uploads/News/201604/news-t1461837308.jpg\" height=\"255\" width=\"481\" alt=\"\" /><br /></p><p><br /></p>');
INSERT INTO go_newscontent VALUES('15', '关于调整个人借记卡ATM跨行转账汇款业务收费标准的公示');
INSERT INTO go_newscontent VALUES('16', '<p>关于8月16日系统升级期间暂停中银开放平台相关服务的公 <br /></p>');
INSERT INTO go_newscontent VALUES('18', '金融创新助力“一带一路”战略 广西利用沿边区位优势推');
INSERT INTO go_newscontent VALUES('19', '证监会主席助理张育军涉嫌严重违纪接受组织调查');
INSERT INTO go_newscontent VALUES('20', '决策层一周内三提抓改革 释放政策红利稳增长 <br />');
INSERT INTO go_newscontent VALUES('21', '决策层一周内三提抓改革 释放政策红利稳增长 A<br />');
INSERT INTO go_newscontent VALUES('22', '决策层一周内三提抓改革 释放政策红利稳增长B');
INSERT INTO go_newscontent VALUES('30', '<ul><li>.<a href=\"http://ongongong.com/news_details.aspx?id=177&amp;classId=88\">【一带一路 共建繁荣】</a></li></ul>');
INSERT INTO go_newscontent VALUES('31', '<ul><li>.<a href=\"http://ongongong.com/news_details.aspx?id=177&amp;classId=88\">【一带一路 共建繁荣】<img src=\"/uploads/News/201605/news-t1462356499.jpg\" alt=\"\" /></a></li></ul>');
INSERT INTO go_newscontent VALUES('32', '<ul><li>.<a href=\"http://ongongong.com/news_details.aspx?id=180&amp;classId=87\">20150812—人民币贬值成</a></li></ul>');
INSERT INTO go_newscontent VALUES('33', '<ul><li>.<a href=\"http://ongongong.com/news_details.aspx?id=180&amp;classId=87\">20150812—人民币贬值成</a></li></ul>');
INSERT INTO go_newscontent VALUES('34', '<ul><li>.<a href=\"http://ongongong.com/news_details.aspx?id=182&amp;classId=89\">汇市日评2015年08月13日</a></li><li>.<a href=\"http://ongongong.com/news_details.aspx?id=181&amp;classId=89\">汇市观潮2015年08月13日</a></li></ul>');
INSERT INTO go_newscontent VALUES('35', '<a href=\"http://ongongong.com/news_details.aspx?id=181&amp;classId=89\">汇市观潮2015年08月13日</a>');
INSERT INTO go_newscontent VALUES('36', '黄金观潮2015年08月13日');
INSERT INTO go_newscontent VALUES('37', '黄金观潮2015年08月14日');
INSERT INTO go_newscontent VALUES('38', '<p><img src=\"/uploads/News/201604/news-t1461899119.jpg\" alt=\"\" /></p><p>雷鸣<br /></p>');
INSERT INTO go_newscontent VALUES('39', '<p><img src=\"/uploads/News/201604/news-t1461899119.jpg\" alt=\"\" /></p><p>雷鸣<br /></p>');
INSERT INTO go_newscontent VALUES('40', '<img src=\"/uploads/News/201605/news-t1462261325.jpg\" alt=\"\" />');

DROP TABLE IF EXISTS go_newsreply;
CREATE TABLE go_newsreply (
   id int(11) NOT NULL auto_increment,
   news_id int(11) NOT NULL,
   nickname varchar(64) NOT NULL,
   addtime int(11) NOT NULL,
   content varchar(255),
   ip varchar(16) NOT NULL,
   status tinyint(1) DEFAULT '1' NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_newsreply VALUES('1', '4', '网友', '2012', 'hello', '192.168.0.254', '1');
INSERT INTO go_newsreply VALUES('2', '4', '中部车网友', '2012', '嗯， 很中肯的一票。', '192.168.0.254', '1');
INSERT INTO go_newsreply VALUES('3', '4', '坏人', '2012', '一定是票手搞的吧。', '192.168.0.254', '1');
INSERT INTO go_newsreply VALUES('4', '4', '好人', '2012', '窃， 你咋晓得呀', '192.168.0.254', '1');
INSERT INTO go_newsreply VALUES('5', '4', '某网友', '2012', '不仅如此，汉兰达的自动空调系统中，还配置有神经网络控制功能，这种更高层次的控制技术，能够根据人体所处环境而精确控制温度。这样，乘员只要操作旋钮或按键，设置所需温度及风机转速，以后一切事情都由自动空调控制系统办理了。
', '192.168.0.254', '1');
INSERT INTO go_newsreply VALUES('6', '2', '网友', '2012', '还不错', '192.168.0.254', '1');

DROP TABLE IF EXISTS go_pages;
CREATE TABLE go_pages (
   id int(11) NOT NULL auto_increment,
   title varchar(128) NOT NULL,
   keywords varchar(128),
   content text,
   addtime int(11) NOT NULL,
   status tinyint(1) DEFAULT '1' NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_pages VALUES('1', '公司简介', '公司简介', '<p><span style=\"font-size:16px;margin: 0px; padding: 0px; border: 0px; outline: 0px; vertical-align: baseline; list-style-type: none; display: inline;\"><span style=\"font-family:微软雅黑, Arial, Helvetica, sans-serif, 宋体;font-size:16px;line-height: 30px; margin: 0px; padding: 0px; border: 0px; outline: 0px; vertical-align: baseline; list-style-type: none;\"><span style=\"white-space:pre\">	</span>&nbsp;北京中农汇通科技有限公司是一家专注于为广大农村市场提供助农取款等服务的大型综合性科技有限公司，公司总部位于北京市丰台区南四环西路188号十区27号楼。<br /><br /></span><span style=\"font-family:微软雅黑, Arial, Helvetica, sans-serif, 宋体;font-size:16px;line-height: 30px; text-indent: 28px;\"><span style=\"white-space:pre\">	</span>北京中农汇通科技有限公司秉承”方便、快捷、惠民”的企业行动文化，以“服务三农、造福三农”为企业使命，凝聚国内多家金融机构的专业性优势，充分整合社会各方资源，专注于为广大农民提供：取款、转账汇款、存款、缴费、购物、订票等全方位的优质服务。</span><br /><br /></span></p><p><span style=\"font-size:16px;margin: 0px; padding: 0px; border: 0px; outline: 0px; vertical-align: baseline; list-style-type: none; display: inline;\"><span style=\"white-space:pre\">	</span>为确保企业使命的顺利实现，北京中农汇通科技有限公司在中国人民银行政策的大力支持下，与通联支付、中信银行等多家专业性金融机构达成战略合作伙伴，公司具有专业的服务团队，建立了完备的协作和服务体系。<br /><br /></span></p><p><span style=\"font-size:16px;margin: 0px; padding: 0px; border: 0px; outline: 0px; vertical-align: baseline; list-style-type: none; display: inline;\"><span style=\"white-space:pre\">	</span>北京中农汇通科技有限公司期待与您携手，为共同实现全国金融服务“村村通”工程和推动区域经济发展做出应有的贡献！</span></p><div><span style=\"font-size:16px;margin: 0px; padding: 0px; border: 0px; outline: 0px; vertical-align: baseline; list-style-type: none; display: inline;\"><br /></span></div>', '1459069617', '1');
INSERT INTO go_pages VALUES('4', '联系我们', '联系我们', '<p><strong><span style=\"font-size:18px;\"><span style=\"font-family:Arial;\"><big>北京中农汇通科技有限公司</big></span></span></strong><span style=\"font-size:undefined\"><span style=\"font-family:Arial;\"><big> <br />联系地址：北京市丰台区南四环西路188号十区27号楼 <br />邮政编码：100000 <br />联系电话：010-60251365 <br />传真号码：010-60251365 <br />官方微信：zhongnonghuitong<br />客服热线：400-650-2113 <br />联系邮箱：</big></span></span><a href=\"mailto:info@ongongong.com\"><span style=\"font-size:undefined\"><span style=\"font-family:Arial;\"><big>info@ongongong.com</big></span></span></a></p><p><span style=\"font-size:18px;\"><strong><span style=\"font-family:Arial;\"><big>中农汇通河南办事处<br /></big></span></strong></span><span style=\"font-size:undefined\"><span style=\"font-family:Arial;\"><big>联系地址：郑州市金水区经三路与红专路交叉口西北角中信大厦11楼 <br />邮政编码：450000<br />联系电话：0371-55558485 <br />传真号码：0371-55558485<br />官方微信：zhongnonghuitong<br />客服热线：400-650-2113 <br />联系邮箱：</big></span></span><span style=\"font-size:undefined\"><a href=\"mailto:info@ongongong.com\"><span style=\"font-family:Arial;\"><big>info@ongongong.com</big></span></a></span><br /></p>', '1459071065', '1');
INSERT INTO go_pages VALUES('9', '摩托化部队，出发!', 'motorbike', '<img src=\"/uploads/Pages/pages-t1461727704.jpg\" alt=\"\" />', '1461727769', '1');
INSERT INTO go_pages VALUES('10', '汽车人， 出发！', '汽车', '<p><img src=\"http://go.com/uploads/Pages/pages-t1461727704.jpg\" alt=\"\" /></p><p><br /></p><p>其实还是摩托部队！<br /></p>', '1461728856', '1');

DROP TABLE IF EXISTS go_products;
CREATE TABLE go_products (
   id int(11) NOT NULL auto_increment,
   categories_id int(8) NOT NULL,
   title varchar(128) NOT NULL,
   keywords varchar(255),
   logo varchar(255),
   info text,
   recommend tinyint(1) NOT NULL,
   sortorder int(11),
   addtime int(11) NOT NULL,
   status tinyint(1) DEFAULT '1' NOT NULL,
   PRIMARY KEY (id)
);

INSERT INTO go_products VALUES('1', '1', '咖啡', '品质第四、信誉至上、物美价廉、互利互惠', '/uploads/Products/201605/Products-t1462261792_thum.jpg', '<p><img src=\"/uploads/Products/201605/Products-t1462261792.jpg\" alt=\"\" /></p><p></p><ul style=\"list-style: none; margin: 0px; padding: 0px 20px 0px 30px; color: rgb(13, 29, 58); font-family: \'lucida Grande\', Verdana, \'Microsoft YaHei\'; font-size: 14px; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: left; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px;\"><li style=\"list-style: disc inside; margin: 0px 0px 30px; padding: 0px;\"><strong>Rebase（变基）</strong>：整合分支的方法之一。变基时可以选择新的加入提交，每一个提交可以选择 PICK、EDIT、SKIP、SQUASH 和 FIXUP 这几个选项。</li></ul><br /><p></p><p><br /></p>', '1', '200', '1462415376', '1');
INSERT INTO go_products VALUES('2', '2', '透明吉他', '品质第三、信誉至上、物美价廉、互利互惠', '/uploads/Products/201605/Products-t1462261725_thum.jpg', '<p><img src=\"/uploads/Products/201605/Products-t1462261725.jpg\" alt=\"\" border=\"0\" /></p><p></p><ul style=\"list-style: none; margin: 0px; padding: 0px 20px 0px 30px; color: rgb(13, 29, 58); font-family: \'lucida Grande\', Verdana, \'Microsoft YaHei\'; font-size: 14px; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: left; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px;\"><li style=\"list-style: disc inside; margin: 0px 0px 30px; padding: 0px;\"><strong>Merge（合并）</strong>：在 WebIDE 的仓库下拉菜单中选择“合并分支”，当出现合并冲突时，会弹出 对比视图，再进行逐一合并。</li><li style=\"list-style: disc inside; margin: 0px 0px 30px; padding: 0px;\"><strong>Stash（储藏）</strong>：在 WebIDE 上使用 git stash 为你保存其中一个分支上暂不合并的代码草稿， 使你后续可以直接回到这个点进行编辑，在后期恢复时可直接恢复到当前分支或新建分支。</li></ul><br /><p></p>', '1', '300', '1462414844', '1');
INSERT INTO go_products VALUES('4', '4', '天马行空', '品质第一、信誉至上、物美价廉、互利互惠', '/uploads/Products/201605/Products-t1462255311_thum.jpg', '<p><img src=\"/uploads/Products/201605/Products-t1462255311.jpg\" alt=\"\" height=\"245\" width=\"437\" /></p><p></p><p style=\"margin: 0px 0px 0.75em; font-size: 16px; line-height: 27.2px; text-indent: 1em; color: rgb(51, 51, 51); font-family: \'Helvetica Neue\', Helvetica, Tahoma, Arial, STXihei, \'Microsoft YaHei\', 微软雅黑, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(254, 254, 254);\">组合使用二者</p><p style=\"margin: 0px 0px 0.75em; font-size: 16px; line-height: 27.2px; text-indent: 1em; color: rgb(51, 51, 51); font-family: \'Helvetica Neue\', Helvetica, Tahoma, Arial, STXihei, \'Microsoft YaHei\', 微软雅黑, sans-serif; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; orphans: auto; text-align: start; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; background-color: rgb(254, 254, 254);\">在我们的具体应用中，通常比较多的是组合使用构造函数模式与原型模式。构造函数用于定义实例属性，原型用于定于共享的属性和方法，这样能够最大限度的节省内存。以下是一个基本的组合使用构造函数与原型的例子</p><br />', '1', '700', '1462414824', '1');
INSERT INTO go_products VALUES('5', '5', '彩陶坊', '白酒', '/uploads/Products/201605/Products-t1462245557.jpg', '<p><img src=\"/uploads/Products/201605/Products-t1462245492.jpg\" alt=\"\" height=\"185\" width=\"331\" /></p><p>123<br /></p>', '0', '500', '1462246928', '0');
INSERT INTO go_products VALUES('6', '6', '纸盒子', '品质第二、信誉至上、物美价廉、互利互惠', '/uploads/Products/201605/Products-t1462259425_thum.jpg', '<p>123...<br /></p><p><img src=\"/uploads/Products/201605/Products-t1462259425.jpg\" alt=\"\" /></p><p><span style=\"font-family:\'lucida Grande\', Verdana, \'Microsoft YaHei\';color:#444444;font-size: 14px; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: left; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px; display: inline !important; float: none; background-color: rgb(255, 255, 255);\">Coding WebIDE 是 Coding 自主研发的在线集成开发环境 ( IDE ) 。你可以通过 WebIDE 创建项目的工作空间, 进行在线开发, 调试等操作，有功能健全的 Terminal。由于 Git 使用门槛偏高, WebIDE 提供了便利的 GUI 界面，在此前, WebIDE 实现了基本的 Git 客户端特性。本次更新，增加了 merge、stash、rebase、reset、 tags 几个高级特性,使得你使用 WebIDE 的效率大大提升！</span><br /></p><p>456...<br /></p>', '1', '600', '1462414837', '1');
INSERT INTO go_products VALUES('7', '6', '凤梨波罗', '品质第五、信誉至上、物美价廉、互利互惠', '/uploads/Products/201605/Products-t1462259623_thum.jpg', '<p><img src=\"/uploads/Products/201605/Products-t1462259623.jpg\" alt=\"\" /></p><p></p><ul style=\"list-style: none; margin: 0px; padding: 0px 20px 0px 30px; color: rgb(13, 29, 58); font-family: \'lucida Grande\', Verdana, \'Microsoft YaHei\'; font-size: 14px; font-style: normal; font-variant: normal; font-weight: normal; letter-spacing: normal; line-height: 24px; orphans: auto; text-align: left; text-indent: 0px; text-transform: none; white-space: normal; widows: 1; word-spacing: 0px; -webkit-text-stroke-width: 0px;\"><li style=\"list-style: disc inside; margin: 0px 0px 30px; padding: 0px;\"><strong>Reset（重置）</strong>：修复你开发错误的方法。对于尚未传播出去的提交，可以使用 WebIDE 的 reset 功能进行代码重置，回退到以往的提交。</li><li style=\"list-style: disc inside; margin: 0px 0px 30px; padding: 0px;\"><strong>Tags（里程碑）</strong>：可直接在 WebIDE 里对提交进行命名。如存在同名 tag，可以设定为强制， 覆盖掉老的 tag。</li></ul><br /><p></p>', '1', '100', '1462414859', '1');

DROP TABLE IF EXISTS go_roles;
CREATE TABLE go_roles (
   id int(11) NOT NULL auto_increment,
   rolename varchar(32) NOT NULL,
   controllers varchar(255) NOT NULL,
   created int(11),
   updated int(11),
   PRIMARY KEY (id)
);

INSERT INTO go_roles VALUES('1', '系统管理员', 'Admin,News,Pages,Products,System', NULL, NULL);
INSERT INTO go_roles VALUES('2', '普通会员', 'Admin', NULL, NULL);
INSERT INTO go_roles VALUES('3', 'EVERYONE', 'Error,Index,Public', NULL, NULL);
INSERT INTO go_roles VALUES('4', '客户组', '', '1461124127', '1461124127');

