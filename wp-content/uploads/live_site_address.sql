insert into wp_options (blog_id, option_name, option_value, autoload)
values
(0,'siteurl','http://66.228.48.16','yes'),
(0,'home','http://66.228.48.16','yes')
on duplicate key update
option_value=values(option_value);
