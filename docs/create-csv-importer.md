# Overview - CSV Importer

We'll start off by creating a new importer based on CSV structure so that we can import our demo banking file based on the CSV format.

As mentioned before, there are a handful of variables that have special weight in the CiviBanking ecosystem. Those are: 

* booking_date
* value_date
* amount
* currency


Those variables are considered mandatory and need to be defined in our importer rule.

Lastly, it's important to mention that all rules must be written in valid JSON format.

## Opening / generic elements

```json
{
 "delimiter": ";",
 "encoding": "UTF8",
 "skip": 2,
 "header": 3,
 "title": "Custom importer",
 "comment": "This is a custom CSV importer",
 "defaults": {
   "source": "CiviBanking Statement",
   "payment_instrument_id": 1
 },
```

### Comments

We start of by adding a comment so that it will make our lives easier in case we have many importers at a later stage. Note: Comment tags are always *optional*.

### Defaults

The group **Defaults** is used to enclose definitions for some standard piece of information about the incoming transactions that will be added to **all** the upcoming transactions of the file/importer that we will be importing.

In this example, we specify that by default the attribute of **source** will always be the source 'CiviBanking Statement' (unless we alter it during the matcher chapter)

### Encoding

We will need to specify an encoding scheme so that we won't have issues while importing the file.  A list of all encodings can be found [here](http://php.net/manual/en/mbstring.supported-encodings.php)

### Delimiter

We need to specify which delimiter to use to separate each column. In our example we use the semicolon (;).

### Skip

(Optional). We can define a number of rows to skip/ignore from the start. It works in conjuction with header (see below).

### Header

This parameter is required to point on to which line the importer needs to read the column headers.

## Rules / Subrules (set of actions)

Rules are the actual part that starts to do the element assignment based on what they 'see' inside the transaction file while importation takes place. They start by definining the attribute 'rules':

```json
"rules": [
]
```

### set (action)

During importation, one of the most commonly used actions is **set**. This action has the following possible parameters/options:

* "from": Defines which column to read so that it can find the variable. if it's not numeric, then we're directing the importer to find that column name instead of the column number.
* "to": Once we get this variable, we need to define where to store it. This is the reason that we use the "to" element. In our example we will be storing it in the variable **"currency"** of the entity/namespace **btx**.
* "type": "set"

---

```json
{
 "comment": "Read the currency from column 1",
 "from": 1,
 "to": "currency",
 "type": "set" 
},
```

---

```json
{
 "comment": "read name of statement",
 "from": "import_name",
 "to": "name",
 "type": "set" 
},
```

---

Note: This variable is one of the few that were referred [here](# Overview - XML Importer)

For a comprehensive list of actions, please read [here](importer-csv-actions.md).

---

### amount (action)

```json
{
  "comment": "Fetch the amount from column 3",
  "from": 3,
  "to": "amount",
  "type": "amount" 
},
```

Although a little bit similar to the **set** action, amount is used when we know that our variable is an amount. The difference is that there are special formatters being used so that it can treat properly the thousands/decimals separators.

Similarly to the set action, we're looking for the amount from the column number 3 and we're storing it to the variable **amount** (Namespace: btx).

This action has the following possible parameters/options:

- "from": Defines which column to read so that it can find the variable. if it's not numeric, then we're directing the importer to find that column name instead of the column number.
- "to": Once we get this variable, we need to define where to store it. This is the reason that we use the "to" element.

For a comprehensive list of actions, please read [here](importer-csv-actions.md).

### strtotime (action)

```json
{
  "from": 4,
  "to": "value_date",
  "type": "strtotime:Y-m-d"
},

```

Dates have their own action, called "strtotime". Similar to the set action, they also require 2 parameters to work:

* "from": Defines which column from the CSV to read so that it can find the variable.
* "to": Once we get this variable, we need to define where to store it. This is the reason that we use the "to" element.
* "type": "strtotime:Y-m-d" : The format to convert the source date to.

---

As discussed on the general documentation file but also at the start of this documentation, value_date and booking_date are a few from the minimum parameters required from civibanking to work properly.

Similarly, we're using the `strtotime` action to populate this variable.

For a comprehensive list of actions, please read [here](importer-csv-actions.md).

### replace (action)

```json
{
  "comment": "DBIT means negative",
  "from": "amount",
  "to": "amount",
  "type": "replace:DBIT:-" 
},
```

Replace action is doing an in-place replacement of a string into another string. The source string is specified directly after the `replace` string. The replacement string is specified directly after the source string. Detailed parameters:

- "from": Defines which column from the CSV to read so that it can find the variable.
- "to": Once we get this variable, we need to define where to store it. This is the reason that we use the "to" element.
- "type": replace:<source_string>:<target_string>

In this specific example, we request that the importer will find the variable in the variable named `amount` inside the imported set and replace the word `DBIT` with `-`.

For a comprehensive list of actions, please read [here](importer-xml-actions.md).

### regex (action)

```json
{
 "comment": "extract IBAN",
 "from": 5,
 "to":"_party_IBAN",
 "warn": 0,
 "type":"regex:#[a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}[a-zA-Z0-9]{0,3} (?P<IBAN>[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16})#"
},
```

Similar to the `set` action, the `regex` action does what it exactly implies: Using a regular expression, it extracts the piece of information that was requested. 

Detailed parameters:

- "from": Defines which column from the CSV to read so that it can find the variable.
- "to": Once we get this variable, we need to define where to store it. This is the reason that we use the "to" element.
- "type": `regex:<regex_expression>`

!!! note "Note1: Regex expressions"
    The regex expression should start and end with the dash sign (#). 

!!! note "Note2: Escaped backslashes"
    Please be careful with the backslashes. You will need to escape it (like in our example, by using the backslash twice).

For a comprehensive list of actions, please read [here](importer-csv-actions.md).

## Complete code

Lets see the whole ruleset and then analyse it step-by-step:

```json
{
   "delimiter":";",
   "encoding":"UTF8",
   "skip":2,
   "header":3,
   "title":"Custom importer",
   "comment":"This is a custom CSV importer",
   "defaults":{
      "source":"CiviBanking Statement",
      "payment_instrument_id":"1"
   },
   "rules":[
      {
         "comment":"Read the currency from column 1",
         "from":1,
         "to":"currency",
         "type":"set"
      },
      {
         "comment":"read name of statement",
         "from":"import_name",
         "to":"name",
         "type":"set"
      },
      {
         "comment":"Fetch the amount from column 3",
         "from":3,
         "to":"amount",
         "type":"amount"
      },
      {
         "from":4,
         "to":"value_date",
         "type":"strtotime:Y-m-d"
      },
      {
         "from":4,
         "to":"booking_date",
         "type":"strtotime:Y-m-d"
      },
      {
         "comment":"DBIT means negative",
         "from":"amount",
         "to":"amount",
         "type":"replace:DBIT:-"
      },
      {
         "comment":"extract IBAN",
         "from":5,
         "to":"_party_IBAN",
         "warn":0,
         "type":"regex:#[a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}[a-zA-Z0-9]{0,3} (?P<IBAN>[a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16})#"
      }
   ]
}
```

1. Define by default that:
   1. the delimiter will be semicolon
   2. encoding of the source file: UTF8
   3. Header is on line 3, but skip line 1 & 2
   4. Field "source" is auto-set to "CiviBanking Statement"
   5. Payment instrument ID is 1
2. Read the currency from column 1
3. Read the variable "name" from column named "import_name"
4. Read the amount from column 3
5. Read from column 4 **booking_date** and **value_date** with the format of **Y-m-d**.
6. If in variable "amount" there is the text "DBIT", replace it with a minus (-) sign.
7. Extract the IBAN using REGEX



