# CBUL

This is a Cluster Based Unsupervised Learning that creates cluster centroids from user inputs. The goal of this project is to use PHP to demonstrate both server side and client side security.

## Installation
This project can be ran with XAMPP as a web server and database. To install follow these steps:
Install from https://www.apachefriends.org/download.html
Start the Apache Web Server and the MySQL database
Move the project to the htdocs in the xampp directory

## Design and Usage

Some security measures and features considered:
* Client side validation
* Sessions
* Salting passwords and hashing
* Sanitizing inputs to prevent SQL injections

![SignUp](https://github.com/julianvu/CBUL/blob/master/images/CBUL-signup.png)

Sign up to MySQL database that validates new user

![CreateModel](https://github.com/julianvu/CBUL/blob/master/images/FirstModelCBUL.png)

Create a model by inputting some coordinates through manual input, text input or both.

![TrainMode](https://github.com/julianvu/CBUL/blob/master/images/TrainModelInput.png)

Train the model by specifying how many clusters you want.

![CalculateCluster](https://github.com/julianvu/CBUL/blob/master/images/CalculatedCentroids.png)

Calculate the centroids of that model using K means algorithm

![TestModel](https://github.com/julianvu/CBUL/blob/master/images/FindNearestCentroid.png)

You can now test the model with some of your own data to see which model it belongs to.

