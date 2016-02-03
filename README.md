# MobWeb_CanceledOrderNotification extension for Magento

Sends an email to the "Send Order Email Copy To" email address when an order is set to a certain status. Useful to receive an instant notification of certain order statuses, e.g. cancellations during payment on an external payment page.

## Configuration

Go to `System -> Transactional Emails` and create the transactional email that should be used to send the notification. Here is an example transactional email template that you can use: 

```
<body style="background:#F6F6F6; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px; margin:0; padding:0;">
    <div style="background:#F6F6F6; font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px; margin:0; padding:0;">
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" valign="top" style="padding:20px 0 20px 0">
                    <table bgcolor="#FFFFFF" cellspacing="0" cellpadding="10" border="0" width="650" style="border:1px solid #E0E0E0;">
                        <!-- [ header starts here] -->
                        <tr>
                            <td valign="top">
                                <a href="{{store url=""}}">
                                    <img src="{{skin url="images/logo_email.gif" _area='frontend'}}" alt="{{var store.getFrontendName()}}"  style="margin-bottom:10px;" border="0"/>
                                </a>
                            </td>
                        </tr>
                        <!-- [ middle starts here] -->
                        <tr>
                            <td valign="top">
                                <p>There is an order with the status of {{var status}}:</p>
                                <h2 style="font-size:18px; font-weight:normal; margin:0;">Order No. {{var order.increment_id}}
                                    <small>(from {{var order.getCreatedAtFormated('long')}})</small>
                                </h2>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <table cellspacing="0" cellpadding="0" border="0" width="650">
                                    <thead>
                                        <tr>
                                            <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px; padding:5px 9px 6px 9px; line-height:1em;">Billing address:</th>
                                            <th width="10"></th>
                                            <th align="left" width="325" bgcolor="#EAEAEA" style="font-size:13px; padding:5px 9px 6px 9px; line-height:1em;">Shipping address:</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td valign="top" style="font-size:12px; padding:7px 9px 9px 9px; border-left:1px solid #EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;">{{var order.getBillingAddress().format('html')}}
                                                <br />
                                                <br />
                                                <p>Payment method: {{var payment }}</p>
                                            </td>
                                            <td>&nbsp;</td>
                                            <td valign="top" style="font-size:12px; padding:7px 9px 9px 9px; border-left:1px solid #EAEAEA; border-bottom:1px solid #EAEAEA; border-right:1px solid #EAEAEA;">{{var order.getShippingAddress().format('html')}}
&nbsp;</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br/>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#EAEAEA" align="center" style="background:#EAEAEA; text-align:center;">
                                <center>
                                    <p style="font-size:12px; margin:0;">This is an automated notification from
                                        <strong>{{var store.getFrontendName()}}</strong>
                                    </p>
                                </center>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
```

Next, go to `System -> Configuration -> Sales Emails -> (Order)` and update the two newly created fields `Order Status Change Notification Template`and `Order Status Change Notification Statuses`.

## Installation

Install using [colinmollenhour/modman](https://github.com/colinmollenhour/modman/).

## Questions? Need help?

Most of my repositories posted here are projects created for customization requests for clients, so they probably aren't very well documented and the code isn't always 100% flexible. If you have a question or are confused about how something is supposed to work, feel free to get in touch and I'll try and help: [info@mobweb.ch](mailto:info@mobweb.ch).