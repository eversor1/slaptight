# SlapTight #

## Introduction ##

SlapTight is a set of classes that provide live and realtime access to a set of MySQL query results. These results can be retrieved from the database in realtime. Changes to the result set can also update to the database in realtime.

## Road Map ##

  * ~~The first major issue is that when a query is done, a simple array of row objects is returned. What should actually be returned is an object that when iterated through or referenced by key, returns the correct row, but can also hold methods for additional operations (inserting etc.)~~ Feature Added in 0.3
  * ~~There is currently no insert feature attached to the result set. This is a priority~~ Feature Added in 0.3