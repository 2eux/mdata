<Worksheet ss:Name="it.mds Request Data" ss:Protected="1">
<Table ss:ExpandedColumnCount="15" ss:ExpandedRowCount="41" x:FullColumns="1" x:FullRows="1" ss:StyleID="s62" ss:DefaultRowHeight="15">
<Column ss:StyleID="s99" ss:Width="120" ss:Span="2"/>
<Column ss:Index="4" ss:StyleID="s99" ss:Width="255.6"/>
<Column ss:StyleID="s99" ss:Width="120" ss:Span="1"/>
<Column ss:Index="7" ss:StyleID="s100" ss:Width="120"/>
<Column ss:StyleID="s99" ss:Width="120" ss:Span="1"/>
<Row ss:AutoFitHeight="0" ss:Height="30" ss:StyleID="s63">
<Cell ss:MergeAcross="14">
<Data ss:Type="String">Source Data for Migration Object: Data Migration it.mds Service (Create and Change)</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:StyleID="s65">
<Cell ss:MergeAcross="14">
<Data ss:Type="String">Version S4CORE 103 - 09.09.2025 © Copyright SAP SE. All rights reserved.</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="12">
<Cell ss:MergeAcross="14" ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Height="13.950000000000001" ss:Hidden="1">
<Cell ss:StyleID="s62">
<Data ss:Type="String">ZMDSREQUEST</Data>
</Cell>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
<Cell ss:StyleID="s62"/>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell>
<Data ss:Type="String">M_COUNTER</Data>
</Cell>
<Cell>
<Data ss:Type="String">ASNUM</Data>
</Cell>
<Cell>
<Data ss:Type="String">SUBTYPE</Data>
</Cell>
<Cell>
<Data ss:Type="String">ASKTX</Data>
</Cell>
<Cell>
<Data ss:Type="String">BKLAS</Data>
</Cell>
<Cell>
<Data ss:Type="String">LVORM</Data>
</Cell>
<Cell>
<Data ss:Type="String">USERF1_NUM</Data>
</Cell>
<Cell>
<Data ss:Type="String">USERF1_TXT</Data>
</Cell>
<Cell>
<Data ss:Type="String">USERF2_TXT</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0" ss:Hidden="1">
<Cell>
<Data ss:Type="String">ETE;30;0;C;30;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;18;0;C;18;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;25;0;C;25;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;40;0;C;40;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;4;0;C;4;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;1;0;C;1;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ENU;10;0;N;10;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;40;0;C;40;0</Data>
</Cell>
<Cell>
<Data ss:Type="String">ETE;10;0;C;10;0</Data>
</Cell>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:MergeAcross="9" ss:StyleID="m525203580">
<Data ss:Type="String"/>
</Cell>
</Row>
<Row ss:AutoFitHeight="0">
<Cell ss:StyleID="s108">
<Data ss:Type="String">Mig. Counter Type: Text Length: 30</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">Activity Number Type: Text Length: 18</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">Subtype Type: Text Length: 25</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">Service Short Text Type: Text Length: 40</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">Valuation Class Type: Text Length: 4</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">Deletion Indicator Type: Text Length: 1</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">User-Defined Field Numeric Type: Number Length: 10</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">User-Defined Field 1 - Text Type: Text Length: 40</Data>
</Cell>
<Cell ss:StyleID="s108">
<Data ss:Type="String">User-Defined Field 2 - Text Type: Text Length: 10</Data>
</Cell>
</Row>

<!-- DATA -->
<?php foreach ($details as $row): ?>

<Row ss:AutoFitHeight="0">

    <!-- M_COUNTER -->
    <Cell>
        <Data ss:Type="String">
            <?= xe($row['mig_counter'] ?? '') ?>
        </Data>
    </Cell>

    <!-- ASNUM -->
    <Cell>
        <Data ss:Type="String">
            <?= xe($row['service_number'] ?? '') ?>
        </Data>
    </Cell>

    <!-- SUBTYPE -->
    <Cell>
        <Data ss:Type="String">
            <?= xe($row['service_category'] ?? '') ?>
        </Data>
    </Cell>

    <!-- ASKTX -->
    <Cell>
        <Data ss:Type="String">
            <?= xe($row['description'] ?? '') ?>
        </Data>
    </Cell>

    <!-- BKLAS -->
    <Cell>
        <Data ss:Type="String">
            <?= xe($row['valuation_class'] ?? '') ?>
        </Data>
    </Cell>

    <!-- kosong sampai column 8 -->
    <Cell ss:Index="8">
        <Data ss:Type="String"></Data>
    </Cell>

</Row>

<?php endforeach; ?>

</Table>
<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
<PageSetup>
<Header x:Margin="0.3"/>
<Footer x:Margin="0.3"/>
<PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
</PageSetup>
<Unsynced/>
<Print>
<ValidPrinterInfo/>
<PaperSizeIndex>9</PaperSizeIndex>
<HorizontalResolution>600</HorizontalResolution>
<VerticalResolution>600</VerticalResolution>
</Print>
<TabColorIndex>51</TabColorIndex>
<FreezePanes/>
<FrozenNoSplit/>
<SplitHorizontal>8</SplitHorizontal>
<TopRowBottomPane>8</TopRowBottomPane>
<SplitVertical>1</SplitVertical>
<LeftColumnRightPane>1</LeftColumnRightPane>
<ActivePane>0</ActivePane>
<Panes>
<Pane>
<Number>3</Number>
</Pane>
<Pane>
<Number>1</Number>
</Pane>
<Pane>
<Number>2</Number>
</Pane>
<Pane>
<Number>0</Number>
<ActiveRow>41</ActiveRow>
</Pane>
</Panes>
<ProtectObjects>False</ProtectObjects>
<ProtectScenarios>False</ProtectScenarios>
<AllowSizeCols/>
<AllowSizeRows/>
</WorksheetOptions>
</Worksheet>