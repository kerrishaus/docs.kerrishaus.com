<div>
<h1>PUT - Status</h1>
<h3>api.kunindustries.com/portal/lms/attendance/status</h3>
<p>
    <strong>Parameters</strong>
    <table>
        <tr>
            <th>Name</th>
            <th>Type</th>
            <th>In</th>
            <th>Description</th>
        </tr>
        <tr>
            <td><code>classid</code></td>
            <td>int</td>
            <td>query</td>
            <td>The numerical ID referring to the LMS class.</td>
        </tr>
        <tr>
            <td><code>userid</code></td>
            <td>int</td>
            <td>query</td>
            <td>The numerical ID referring to the student.</td>
        </tr>
        <tr>
            <td><code>status</code></td>
            <td>int</td>
            <td>query</td>
            <td>The status ID the student will be marked with.</td>
        </tr>
    </table>
    <hr/>

    <strong>Statuses</strong>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
        </tr>
        <tr>
            <td>0</td>
            <td>Online</td>
            <td>Whenver the student has logged into their computer.</td>
        </tr>
        <tr>
            <td>1</td>
            <td>Away</td>
            <td>Computer is asleep or locked.</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Offline</td>
            <td>Not logged into the computer at all</td>
        </tr>
        </tr>
    </table>
    <hr/>
    
    <strong>Responses</strong>
    <table>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Description</th>
        </tr>
        <tr>
            <td>200</td>
            <td>OK</td>
            <td>The API call was successful.</td>
        </tr>
        <tr>
            <td>400</td>
            <td>Invalid request.</td>
            <td>A query parameter was invalid.</td>
        </tr>
    </table>
    <hr/>
</p>
</div>
