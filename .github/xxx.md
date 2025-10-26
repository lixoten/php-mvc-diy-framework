

added some new fields to testys table to handle new fields types we had not covered yer
telephone string:
    - For special validation rules on server side,
    - imask on the client side
    - in config file for tel, sanitize Closure to strip mask from imask
date_of_birth date:
    - we were missing DateType and Date validations
interest_soccer_indicator bool:
interest_hockey_indicator bool:
    - to test checkboxes
is_verified bool
    - ???????????????????????/
gender_id string:
    - to test a dropdown
gender_other string:
    - to work in inconjunction with gender_id

Fixes to Migration to work with some of these new fields


<input>

type="text"

type="password"

type="email"

type="tel"

type="url"

type="number"

type="range"

type="date"

type="datetime"

type="time"

type="week"

type="month"

type="color"

type="checkbox"

type="radio"

type="file"

type="hidden"

<textarea>

<select>

option

<button>

<datalist>
