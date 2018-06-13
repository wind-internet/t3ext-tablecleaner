.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


What does it do?
----------------
Removes [deleted/hidden] records older than [N] days from tables.

When a record is deleted in TYPO3, most of the times it is not actually deleted. Many tables just set a value of 1 on the 'deleted' field. Some sites can get pretty big and a lot of content is added and removed. This means that some tables will fill up with deleted records.

There are also tables that just keep on growing. Take sys_log for example. That logs system events from the time the system was first started to the present day. In some installations the sys_log also contains a lot of PHP error messages. I have seen sys_log tables of multiple GigaBytes in size.

Also sometimes editors mark content 'hidden' to re-use it on some later date. This day may never come. It Is not uncommon to see hidden content from two years ago that is just sitting there taking up valuable space.

This extension provides a scheduler task for 'cleaning up' ever growing tables by supplying three different cleanup tasks:

- Remove deleted entries from the database older than N days
- Remove (or mark as deleted) hidden entries from the database older than N days
- Remove any entries from the database older than N days

For these tasks a 'tstamp' field is required. If this field is not present in the table, it will not show up in the table listings.
