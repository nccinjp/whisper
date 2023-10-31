create table user(
	userId varchar(30),
	userName varchar(20) not null,
	password varchar(64) not null,
	profile varchar(200) default "",
	iconPath varchar(100),
	primary key(userId)
);

create table follow(
	userId varchar(30),
	followUserId varchar(30),
	primary key(userId,followUserId)
);

create table whisper(
	whisperNo bigint auto_increment,
	userId varchar(30) not null,
	postDate date not null default (now()),
	content varchar(256) not null,
	imagePath varchar(100) ,
	primary key(whisperNo)
);

create table goodInfo(
	userId varchar(30),
	whisperNo bigint,
	primary key(userId,whisperNo)
);


create view goodCntView (whisperNo, cnt) as select whisperNo, count(userId) from goodinfo group by whisperNo;
create view whisperCntView(userId, cnt) as Select userId, count(content) from whisper group by userId;
create view followerCntView (followUserId, cnt) as select followUserId, count(userId) from follow group by followUserId;
create view followCntView (userId, cnt) as select userId, count(followUserId) from follow group by userId;

