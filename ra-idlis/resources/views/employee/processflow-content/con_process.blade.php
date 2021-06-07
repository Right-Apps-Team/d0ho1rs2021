<table>
    <tr>
        <td>
            <h1>1</h1>
        </td>
        <td>
            <small><b>Client</b></small>
            <p>Client Registration</p>

        </td>

    </tr>
    <tr>
        <td>
            <h1>2</h1>
        </td>
        <td>
            <p>Application</p>
        </td>
    </tr>
    <tr>
        <td>
            <h1>3</h1>
        </td>
        <td>
            <p>Input Requirements</p>
        </td>
    </tr>
    <tr>
        <td>
            <h1>4</h1>
        </td>
        <td>
            <small><b>RLO/Div Chief</b></small>
            <p>Documentary Evaluation</p>
        </td>
         @if ($grpid == 'NA' || $grpid == "PO" || $grpid == "FDA") 
        <td><a href="{{asset('/employee/dashboard/processflow/evaluate')}}"><i class="fa fa-angle-right" aria-hidden="true"></i></a></td>
         @endif 
    </tr>
    <tr>
        <td>
            <h1>5</h1>
        </td>
        <td>
            <small><b>Client</b></small>
            <p>Payment (Auto Generated Order of Payment)</p>
        </td>

    </tr>

    <tr>
        <td>
            <h1>6</h1>
        </td>
        <td>
            <small><b>Cashier</b></small>
            <p>Payment Confirmation</p>
        </td>
         @if($grpid == 'NA' || $grpid == 'CS') 
        <td><a href="{{asset('employee/dashboard/processflow/cashier')}}"><i class="fa fa-angle-right" aria-hidden="true"></i></a></td>
         @endif 
    </tr>
    <tr>
        <td>
            <h1>7</h1>
        </td>
        <td>
            <small><b>RLO/Div Chief</b></small>
            <p>Committee Assignment</p>
        </td>
        @if($grpid == 'NA' || $grpid == 'DC')
        <td><a href="{{asset('/employee/dashboard/processflow/assignmentofcommittee')}}"><i class="fa fa-angle-right" aria-hidden="true"></i></a></td>
         @endif 
    </tr>
    <tr>
        <td>
            <h1>8</h1>
        </td>
        <td>
            <p>CON Evaluation</p>
        </td>
        @if($grpid == 'NA' || $grpid == 'DC') 
        <td><a href="{{asset('employee/dashboard/processflow/conevaluation')}}"><i class="fa fa-angle-right" aria-hidden="true"></i></a></td>
         @endif 
    </tr>
    <tr>
        <td>
            <h1>9</h1>
        </td>
        <td>
            <small><b>Div Chief</b></small>
            <p>Recommendation for Approval</p>
        </td>
         @if ($grpid == 'NA' || $grpid == "DC") 
        <td><a href="{{ asset('/employee/dashboard/processflow/recommendation') }}"><i class="fa fa-angle-right" aria-hidden="true"></i></a></td>
         @endif 
    </tr>
    <tr>
        <td>
            <h1>10</h1>
        </td>
        <td>
            <small><b>Director</b></small>
            <p>Approval/Issuance of Certificate</p>
        </td>
        @if($grpid == 'NA' || $grpid == 'DR' || $grpid == 'PO') 
        <td><a href="{{asset('/employee/dashboard/processflow/approval')}}"><i class="fa fa-angle-right" aria-hidden="true"></i></a></td>
         @endif 
    </tr>
  
</table>