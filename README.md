# [Labs64 NetLicensing](http://netlicensing.io) FastSpring integration

Visit Labs64 NetLicensing at http://netlicensing.io

## Configure your product within FastSpring

To integrate for one-time product purchase (Licensing Model _"..."_), follow these steps:

### 1. Add a new "Fulfillment Action" to the product
![Figure p01](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/p01.png)

### 2. Choose "Generate a License" -> "Script (PHP or JavaScript)"
![Figure p02](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/p02.png)

### 3. On the next step for "Script Type" choose "PHP", leave other parameters unchanged, click "Create"
![Figure p03](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/p03.png)

### 4. Paste content of the file "product_onetime.php" to the opened text field "Script Source Code (PHP)"
![Figure p04](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/p04.png)

### 5. At the top of the script edit "user configuration" section
![Figure p05](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/p05.png)

### 6. Save Fulfillment Action
![Figure p06](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/p06.png)


## Configure your product within FastSpring (Licensing Model _"Subscription"_)

To integrate for recuring purchase (Licensing Model "Subscription"), follow these steps:

### 1. Add a new "Fulfillment Action" to the product
![Figure s01](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s01.png)

### 2. Choose "Generate a License" -> "Script (PHP or JavaScript)"
![Figure s02](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s02.png)

### 3. On the next step for "Script Type" choose "PHP", leave other parameters unchanged, click "Create"
![Figure s03](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s03.png)

### 4. Paste content of the file "product_subscription.php" to the opened text field "Script Source Code (PHP)"
![Figure s04](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s04.png)

### 5. At the top of the script edit "user configuration" section
![Figure s05](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s05.png)

### 6. Save Fulfillment Action
![Figure s06](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s06.png)

### 7. Return to home screen, choose "Custom Fields" and add a new one
![Figure s07](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s07.png)

### 8. Name the new field "custom_referrer", click "Next"
![Figure s08](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s08.png)

### 9. Set "Display name" and add new "Active Form Field"
![Figure s09](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s09.png)

### 10. Fill the next form as in the screenshot and save the new field and the form design
![Figure s10](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s10.png)

### 11. Edit "Conditions", set to "Order Environment Condition", set "Applies When" -> "Environment Tag Exists" to "licensee_number"
![Figure s11](https://raw.githubusercontent.com/Labs64/NetLicensingClient-FastSpring/master/images/s11.png)

### 12. Create condition and save the field
