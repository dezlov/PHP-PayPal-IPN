# Change Log

This change log contains the highlights of major changes. For details on all finer changes please check the commits history.

## v3.0.0

- Added a sample report.
  - As returned by the `getTextReport()` method.
- Added 5 example scripts.
  - Ranging from basic IPN verification to a complete order processing chain.
- Added user defined path to Certificate Authority (CA) bundle file.
- Reformatted output of `getTextReport()` and added a list of used options. 
- Added `getData()` and `getRawData()` methods.
  - Deprecated `getPostData()` and `getRawPostData()` methods in favor of the new methods.
- Fixed data mishandling issues in `processIpn()`.
- Code reformatting.
- Fixed "*Undefined index: REQUEST_METHOD*" when calling `requirePostMethod()` from CLI.
- Make POST method requirement optional via `requirePostMethod` property.
  - Ignore POST method requirement when data is supplied via a parameter.
- Added `tryProcessIpn()` for processing IPN requests without having to handle exceptions.
  - Internally it calls `processIpn()` but catches any exceptions.
- Restored original error handling in `processIpn()`.
  - It once again throws an exception when an error occurs, like in the original version.
- Fixed "*invalid argument supplied for foreach()*" in `getTextReport()`.
- Rebranding for a new fork.
  - Changed namespace to `dezlov\PayPal`. 

## v2.5.2

Forked from [WadeShuler/PHP-PayPal-IPN](https://github.com/WadeShuler/PHP-PayPal-IPN).

## v2.1.0

Forked from [Quixotix/PHP-PayPal-IPN](https://github.com/Quixotix/PHP-PayPal-IPN).
