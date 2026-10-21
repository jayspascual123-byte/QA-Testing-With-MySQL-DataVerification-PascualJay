# QA Testing With MySQL Data Verification

## Repository Overview

This repository documents a quality-assurance test of the **Kara David's Wish Note** web application. The exercise checks form behavior, confirmation modal behavior, server-side validation, and whether submitted values are correctly stored in MariaDB/MySQL.

## Repository Contents

- **Part A — `readme.md`:** Test case documentation and execution results.
- **Part B — `webapp-generation-prompt.txt`:** The prompt used to generate the test web application.
- **Part C — `test-webapp.php`:** The single-file PHP application containing HTML, CSS, JavaScript, and MySQL logic.
- **`TestCase.txt`:** Plain-text test case record, if included in the working files.
- **Required filename reference:** `textcase.txt`

## Test Case Documentation

**Test Case ID:** TC-DataEntry-001  
**Test Case Title:** Data Entry - Valid  
**Description:** Functional testing of the Data Entry page with backend validation using MySQL queries.  
**Preconditions:** The Kara David Birthday Wishnote application is installed locally, the database is pre-populated for comparison, and the Data Entry page is opened in a current Chrome or Firefox browser.  
**Test Data:** Five valid entries containing First Name, Last Name, Government Position, and Location within the maximum character limits.

## Test Steps and Results

| Step | Action | Expected Result | Actual Result |
|---|---|---|---|
| a | Verify that the form contains labels in proper order and the text boxes are writable. | Labels appear in this order: “Target's First Name,” “Target's Last Name,” “Target's Government Position,” and “Target's Location”; all text boxes are writable. | The form contains labels in the required order and all text boxes are writable. |
| b | Verify the “Send Wish Note” button. | The button changes color on hover and is enabled. | The button changes color on hover and is enabled. |
| c | Enter valid test data into the respective fields. | All fields accept valid entries within their maximum character limits. | The respective fields accept valid test data. |
| d | Click “Send Wish Note” and inspect the modal. | The “Verify Target Data” modal appears and displays the values entered for all four fields. | Pending Execution |
| e | Verify the values shown in the modal. | The modal values match the entered First Name, Last Name, Government Position, and Location. | Pending Execution |
| f | Click “Cancel” in the modal. | The modal closes and no record is submitted. | Pending Execution |
| g | Click “Send Wish Note” again, then click “Confirm & Send.” | The modal closes and the form submits the data to the PHP backend. | Pending Execution |
| h | Check the page after submission. | A success message appears: “Success! The wish note has been sent.” if the database insert succeeds. | Pending Execution |
| i | Verify the submitted test data in the database using a MySQL query. | The submitted record appears in `main_corrupt_politicians`. The test should compare all submitted fields and identify any missing value. | Pending Execution |

## MySQL Data Verification

Log in to MySQL/MariaDB and select the database:

```sql
USE birthday_wishnote;
SELECT * FROM main_corrupt_politicians;
```

To check whether the Location column is present and populated, inspect the table structure and compare the row with the submitted test data:

```sql
DESCRIBE main_corrupt_politicians;
SELECT FirstName, LastName, GovPosition, Location
FROM main_corrupt_politicians;
```

**Intentional bug to investigate:** The generated PHP INSERT statement inserts only `FirstName`, `LastName`, and `GovPosition`. It omits `Location` from both the SQL column list and the bound parameters, even though the form captures Location. Depending on the table definition, Location may be left blank/defaulted or the insert may fail if the column is required without a default.

**Important:** The successful message alone does not prove that all fields were stored correctly. Verify the database contents before marking the relevant test as passed. Replace “Pending Execution” only after performing the steps and recording the observed results.

## Overall Status

**Status:** Pending execution of steps d–i and database verification.

## Notes

This is a local QA exercise. A MySQL root account with a blank password is insecure and must not be used in production.
