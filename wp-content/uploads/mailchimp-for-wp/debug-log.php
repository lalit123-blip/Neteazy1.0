<?php exit; ?>
[2021-02-12 06:51:01] ERROR: Form 1027 > Mailchimp API error: 400 Bad Request. Invalid Resource. test@gm***.com looks fake or invalid, please enter a real email address.

Request: 
POST https://us1.api.mailchimp.com/3.0/lists/ee857c3e5d/members

{"status":"pending","email_address":"test@gm***.com","interests":{},"merge_fields":{},"email_type":"html","ip_signup":"171.61.26.229","tags":[]}

Response: 
400 Bad Request
{"type":"https://mailchimp.com/developer/marketing/docs/errors/","title":"Invalid Resource","status":400,"detail":"test@gm***.com looks fake or invalid, please enter a real email address.","instance":"0cae1ac2-7415-4d57-835e-c16d62760173"}
[2021-02-12 06:51:18] WARNING: Form 1027 > dev3********@gm***.com is already subscribed to the selected list(s)
[2021-03-16 09:19:38] ERROR: Form 1027 > Mailchimp API error: 400 Bad Request. Invalid Resource. test@gm***.com looks fake or invalid, please enter a real email address.

Request: 
POST https://us1.api.mailchimp.com/3.0/lists/ee857c3e5d/members

{"status":"pending","email_address":"test@gm***.com","interests":{},"merge_fields":{},"email_type":"html","ip_signup":"27.7.254.180","tags":[]}

Response: 
400 Bad Request
{"type":"https://mailchimp.com/developer/marketing/docs/errors/","title":"Invalid Resource","status":400,"detail":"test@gm***.com looks fake or invalid, please enter a real email address.","instance":"12b7710c-5748-4c01-97d5-4c5bf8d900aa"}
