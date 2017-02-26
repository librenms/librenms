<?php
/*************************************************************************************
 * aimms.php
 * --------
 * Author: Guido Diepen (guido.diepen@aimms.com)
 * Copyright: (c) 2011 Guido Diepen (http://www.aimms.com)
 * Release Version: 1.0.8.12
 * Date Started: 2011/05/05
 *
 * AIMMS language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/07/14 (1.0.0)
 *  -  First Release
 *
 * TODO (updated 2004/07/14)
 * -------------------------
 * * Make sure the last few function I may have missed
 *   (like eval()) are included for highlighting
 * * Split to several files - php4, php5 etc
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array(
    'LANG_NAME' => 'AIMMS3',
    'COMMENT_SINGLE' => array(1 => '!'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'HARDQUOTE' => array("'", "'"),
    'HARDESCAPE' => array("'", "\\"),
    'HARDCHAR' => "\\",
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(),
    'STRICT_MODE_APPLIES' => GESHI_MAYBE,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array(),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            'if', 'then', 'else', 'endif', 'elseif', 'for', 'do', 'while' , 'endfor' , 'endwhile', 'break', 'switch', 'endswitch',
            'display', 'return', 'in', 'apply'

            ),
        2 => array(
            'main model' , 'declaration section', 'procedure', 'endprocedure', 'endmodel', 'endsection' , 'set', 'parameter',
            'string parameter', 'element parameter', 'quantity'
            ),
        3 => array(
            'identifier', 'index', 'index domain', 'body'
            ),
        4 => array(
            'ActiveCard','Card','ConvertUnit','DistributionCumulative','DistributionDensity','DistributionDeviation',
            'DistributionInverseCumulative','DistributionInverseDensity','DistributionKurtosis','DistributionMean',
            'DistributionSkewness','DistributionVariance','Element','EvaluateUnit','First','FormatString','Last',
            'Ord','Unit','Val','Aggregate','AttributeToString','CaseCompareIdentifier','CaseCreateDifferenceFile',
            'CloseDataSource','CreateTimeTable','ConstraintVariables','ConvertReferenceDate','CloneElement',
            'FindNthString','FindReplaceNthString','FindReplaceStrings','FindString','StringOccurrences',
            'CurrentToMoment','CurrentToString','CurrentToTimeSlot','DaylightsavingEndDate','DaylightsavingStartDate',
            'DeclaredSubset','DomainIndex','IndexRange','IsRunningAsViewer','ListingFileCopy','ListingFileDelete',
            'DirectoryGetFiles','DirectoryGetSubdirectories','DirectSQL','Disaggregate','ElementCast','ElementRange',
            'EnvironmentGetString','EnvironmentSetString','errh::Adapt','errh::Attribute','errh::Category',
            'errh::Code','errh::Column','errh::CreationTime','errh::Filename','errh::InsideCategory',
            'errh::IsMarkedAsHandled','errh::Line','errh::MarkAsHandled','errh::Message','errh::Multiplicity',
            'errh::Node','errh::NumberOfLocations','errh::Severity','ExcelAddNewSheet','ExcelAssignParameter',
            'ExcelAssignSet','ExcelAssignTable','ExcelAssignValue','ExcelClearRange','ExcelCloseWorkbook',
            'ExcelColumnName','ExcelColumnNumber','ExcelCopyRange','ExcelCreateWorkbook','ExcelDeleteSheet',
            'ExcelPrint','ExcelRetrieveParameter','ExcelRetrieveSet','ExcelRetrieveTable','ExcelRetrieveValue',
            'ExcelRunMacro','ExcelSaveWorkbook','ExcelSetActiveSheet','ExcelSetUpdateLinksBehavior',
            'ExcelSetVisibility','FindUsedElements','GenerateCUT','GMP::Coefficient::Get',
            'GMP::Coefficient::GetQuadratic','GMP::Coefficient::Set','GMP::Coefficient::SetQuadratic',
            'GMP::Column::Add','GMP::Column::Delete','GMP::Column::Freeze','GMP::Column::GetLowerbound',
            'GMP::Column::GetScale','GMP::Column::GetStatus','GMP::Column::GetType','GMP::Column::GetUpperbound',
            'GMP::Column::SetAsObjective','GMP::Column::SetLowerbound','GMP::Column::SetType',
            'GMP::Column::SetUpperbound','GMP::Column::Unfreeze','GMP::Instance::AddIntegerEliminationRows',
            'GMP::Instance::CalculateSubGradient','GMP::Instance::Copy','GMP::Instance::CreateDual',
            'GMP::Instance::CreateMasterMip','GMP::Instance::CreatePresolved',
            'GMP::SolverSession::CreateProgressCategory','GMP::Instance::CreateProgressCategory',
            'GMP::Instance::CreateSolverSession','GMP::Stochastic::CreateBendersRootproblem',
            'GMP::Instance::Delete','GMP::Instance::DeleteIntegerEliminationRows',
            'GMP::Instance::DeleteSolverSession','GMP::Instance::FindApproximatelyFeasibleSolution',
            'GMP::Instance::FixColumns','GMP::Instance::Generate','GMP::Instance::GenerateRobustCounterpart',
            'GMP::Instance::GenerateStochasticProgram','GMP::SolverSession::GetCallbackInterruptStatus',
            'GMP::SolverSession::WaitForCompletion','GMP::SolverSession::WaitForSingleCompletion',
            'GMP::SolverSession::ExecutionStatus','GMP::Instance::GetDirection','GMP::Instance::GetLinearObjective',
            'GMP::Instance::GetMathematicalProgrammingType','GMP::Instance::GetMemoryUsed',
            'GMP::Instance::GetNumberOfColumns','GMP::Instance::GetNumberOfIndicatorRows',
            'GMP::Instance::GetNumberOfIntegerColumns','GMP::Instance::GetNumberOfNonlinearColumns',
            'GMP::Instance::GetNumberOfNonlinearNonzeros','GMP::Instance::GetNumberOfNonlinearRows',
            'GMP::Instance::GetNumberOfNonzeros','GMP::Instance::GetNumberOfRows',
            'GMP::Instance::GetNumberOfSOS1Rows','GMP::Instance::GetNumberOfSOS2Rows',
            'GMP::Instance::GetObjective','GMP::Instance::GetOptionValue','GMP::Instance::GetSolver',
            'GMP::Instance::GetSymbolicMathematicalProgram','GMP::Instance::MemoryStatistics',
            'GMP::Instance::Rename','GMP::Instance::SetCallbackAddCut','GMP::Instance::SetCallbackBranch',
            'GMP::Instance::SetCallbackHeuristic','GMP::Instance::SetCallbackIncumbent',
            'GMP::Instance::SetCallbackIterations','GMP::Instance::SetCallbackNewIncumbent',
            'GMP::Instance::SetCallbackStatusChange','GMP::Instance::SetCutoff','GMP::Instance::SetDirection',
            'GMP::Instance::SetMathematicalProgrammingType','GMP::Instance::SetSolver','GMP::Instance::Solve',
            'GMP::Stochastic::GetObjectiveBound','GMP::Stochastic::GetRelativeWeight',
            'GMP::Stochastic::GetRepresentativeScenario','GMP::Stochastic::UpdateBendersSubproblem',
            'GMP::Linearization::Add','GMP::Linearization::AddSingle','GMP::Linearization::Delete',
            'GMP::Linearization::GetDeviation','GMP::Linearization::GetDeviationBound',
            'GMP::Linearization::GetLagrangeMultiplier','GMP::Linearization::GetType',
            'GMP::Linearization::GetWeight','GMP::Linearization::RemoveDeviation',
            'GMP::Linearization::SetDeviationBound','GMP::Linearization::SetType',
            'GMP::Linearization::SetWeight','GMP::ProgressWindow::DeleteCategory',
            'GMP::ProgressWindow::DisplayLine','GMP::ProgressWindow::DisplayProgramStatus',
            'GMP::ProgressWindow::DisplaySolver','GMP::ProgressWindow::DisplaySolverStatus',
            'GMP::ProgressWindow::FreezeLine','GMP::ProgressWindow::UnfreezeLine',
            'GMP::QuadraticCoefficient::Get','GMP::QuadraticCoefficient::Set','GMP::Row::Activate',
            'GMP::Stochastic::AddBendersFeasibilityCut','GMP::Stochastic::AddBendersOptimalityCut',
            'GMP::Stochastic::BendersFindFeasibilityReference','GMP::Stochastic::MergeSolution',
            'GMP::Row::Add','GMP::Row::Deactivate','GMP::Row::Delete','GMP::Row::DeleteIndicatorCondition',
            'GMP::Row::Generate','GMP::Row::GetConvex','GMP::Row::GetIndicatorColumn',
            'GMP::Row::GetIndicatorCondition','GMP::Row::GetLeftHandSide','GMP::Row::GetRelaxationOnly',
            'GMP::Row::GetRightHandSide','GMP::Row::GetScale','GMP::Row::GetStatus','GMP::Row::GetType',
            'GMP::Row::SetConvex','GMP::Row::SetIndicatorCondition','GMP::Row::SetLeftHandSide',
            'GMP::Row::SetRelaxationOnly','GMP::Row::SetRightHandSide','GMP::Row::SetType',
            'GMP::Solution::Check','GMP::Solution::Copy','GMP::Solution::Count','GMP::Solution::Delete',
            'GMP::Solution::DeleteAll','GMP::Solution::GetColumnValue','GMP::Solution::GetCPUSecondsUsed',
            'GMP::Solution::GetDistance','GMP::Solution::GetFirstOrderDerivative',
            'GMP::Solution::GetIterationsUsed','GMP::Solution::GetNodesUsed','GMP::Solution::GetLinearObjective',
            'GMP::Solution::GetMemoryUsed','GMP::Solution::GetObjective','GMP::Solution::GetPenalizedObjective',
            'GMP::Solution::GetProgramStatus','GMP::Solution::GetRowValue','GMP::Solution::GetSolutionsSet',
            'GMP::Solution::GetSolverStatus','GMP::Solution::IsDualDegenerated','GMP::Solution::IsInteger',
            'GMP::Solution::IsPrimalDegenerated','GMP::Solution::SetMIPStartFlag','GMP::Solution::Move',
            'GMP::Solution::RandomlyGenerate','GMP::Solution::RetrieveFromModel',
            'GMP::Solution::RetrieveFromSolverSession','GMP::Solution::SendToModel',
            'GMP::Solution::SendToModelSelection','GMP::Solution::SendToSolverSession',
            'GMP::Solution::SetIterationCount','GMP::Solution::SetProgramStatus','GMP::Solution::SetSolverStatus',
            'GMP::Solution::UpdatePenaltyWeights','GMP::Solution::ConstructMean',
            'GMP::SolverSession::AsynchronousExecute','GMP::SolverSession::Execute',
            'GMP::SolverSession::Interrupt','GMP::SolverSession::AddLinearization',
            'GMP::SolverSession::GenerateBranchLowerBound','GMP::SolverSession::GenerateBranchUpperBound',
            'GMP::SolverSession::GenerateBranchRow','GMP::SolverSession::GenerateCut',
            'GMP::SolverSession::GenerateBinaryEliminationRow','GMP::SolverSession::GetCPUSecondsUsed',
            'GMP::SolverSession::GetHost','GMP::SolverSession::GetInstance',
            'GMP::SolverSession::GetIterationsUsed','GMP::SolverSession::GetNodesLeft',
            'GMP::SolverSession::GetNodesUsed','GMP::SolverSession::GetNodeNumber',
            'GMP::SolverSession::GetNodeObjective','GMP::SolverSession::GetNumberOfBranchNodes',
            'GMP::SolverSession::GetLinearObjective','GMP::SolverSession::GetMemoryUsed',
            'GMP::SolverSession::GetObjective','GMP::SolverSession::GetOptionValue',
            'GMP::SolverSession::GetProgramStatus','GMP::SolverSession::GetSolver',
            'GMP::SolverSession::GetSolverStatus','GMP::SolverSession::RejectIncumbent',
            'GMP::Event::Create','GMP::Event::Delete','GMP::Event::Reset','GMP::Event::Set',
            'GMP::SolverSession::SetObjective','GMP::SolverSession::SetOptionValue',
            'GMP::Instance::SetCPUSecondsLimit','GMP::Instance::SetIterationLimit',
            'GMP::Instance::SetMemoryLimit','GMP::Instance::SetOptionValue','GMP::Tuning::SolveSingleMPS',
            'GMP::Tuning::TuneMultipleMPS','GMP::Tuning::TuneSingleGMP',
            'GMP::Solver::GetAsynchronousSessionsLimit','GMP::Robust::EvaluateAdjustableVariables',
            'GenerateXML','GetDatasourceProperty','ReadGeneratedXML','ReadXML','ReferencedIdentifiers',
            'WriteXML','IdentifierAttributes','IdentifierDimension','IsRuntimeIdentifier','IdentifierMemory',
            'IdentifierMemoryStatistics','IdentifierText','IdentifierType','IdentifierUnit','ScalarValue',
            'SectionIdentifiers','SubRange','MemoryInUse','CommitTransaction','RollbackTransaction',
            'MemoryStatistics','me::AllowedAttribute','me::ChangeType','me::ChangeTypeAllowed','me::Children',
            'me::ChildTypeAllowed','me::Compile','me::Create','me::CreateLibrary','me::Delete','me::ExportNode',
            'me::GetAttribute','me::ImportLibrary','me::ImportNode','me::IsRunnable','me::Move','me::Parent',
            'me::Rename','me::SetAttribute','MomentToString','MomentToTimeSlot','OptionGetValue',
            'OptionGetKeywords','OptionGetString','OptionSetString','OptionSetValue','PeriodToString',
            'ProfilerContinue','ProfilerPause','ProfilerRestart','RestoreInactiveElements',
            'RetrieveCurrentVariableValues','SetAddRecursive','SetElementAdd','SetElementRename',
            'SQLColumnData','SQLCreateConnectionString','SQLDriverName','SQLNumberOfColumns',
            'SQLNumberOfDrivers','SQLNumberOfTables','SQLNumberOfViews','SQLTableName','SQLViewName',
            'StartTransaction','StringToElement','StringToMoment','StringToTimeSlot','TestDatabaseColumn',
            'TestDatabaseTable','TestDataSource','TestDate','TimeslotCharacteristic','TimeslotToMoment',
            'TimeslotToString','TimeZoneOffset','VariableConstraints','PageOpen','PageOpenSingle','PageClose',
            'PageGetActive','PageSetFocus','PageGetFocus','PageSetCursor','PageRefreshAll','PageGetChild',
            'PageGetParent','PageGetNext','PageGetPrevious','PageGetNextInTreeWalk','PageGetUsedIdentifiers',
            'PageGetTitle','PageGetAll','PageCopyTableToClipboard','PageCopyTableToExcel','PrintPage',
            'PrintPageCount','PrintStartReport','PrintEndReport','PivotTableReloadState','PivotTableSaveState',
            'PivotTableDeleteState','FileSelect','FileSelectNew','FileDelete','FileExists','FileCopy',
            'FileMove','FileView','FileEdit','FilePrint','FileTime','FileTouch','FileAppend','FileGetSize',
            'DirectorySelect','DirectoryCreate','DirectoryDelete','DirectoryExists','DirectoryCopy',
            'DirectoryMove','DirectoryGetCurrent','DialogProgress','DialogMessage','DialogError',
            'StatusMessage','DialogAsk','DialogGetString','DialogGetDate','DialogGetNumber','DialogGetElement',
            'DialogGetElementByText','DialogGetElementByData','DialogGetPassword','DialogGetColor','CaseNew',
            'CaseFind','CaseCreate','CaseLoadCurrent','CaseMerge','CaseLoadIntoCurrent','CaseSelect',
            'CaseSelectNew','CaseSetCurrent','CaseSave','CaseSaveAll','CaseSaveAs','CaseSelectMultiple',
            'CaseGetChangedStatus','CaseSetChangedStatus','CaseDelete','CaseGetType','CaseGetDatasetReference',
            'CaseWriteToSingleFile','CaseReadFromSingleFile','DatasetNew','DatasetFind','DatasetCreate',
            'DatasetLoadCurrent','DatasetMerge','DatasetLoadIntoCurrent','DatasetSelect','DatasetSelectNew',
            'DatasetSetCurrent','DatasetSave','DatasetSaveAll','DatasetSaveAs','DatasetGetChangedStatus',
            'DatasetSetChangedStatus','DatasetDelete','DatasetGetCategory','DataFileGetName',
            'DataFileGetAcronym','DataFileSetAcronym','DataFileGetComment','DataFileSetComment',
            'DataFileGetPath','DataFileGetTime','DataFileGetOwner','DataFileGetGroup','DataFileReadPermitted',
            'DataFileWritePermitted','DataFileExists','DataFileCopy','DataCategoryContents','CaseTypeContents',
            'CaseTypeCategories','Execute','OpenDocument','TestInternetConnection','GeoFindCoordinates',
            'ShowHelpTopic','Delay','ScheduleAt','ExitAimms','SessionArgument','SessionHasVisibleGUI',
            'ProjectDeveloperMode','DebuggerBreakpoint','ShowProgressWindow','ShowMessageWindow',
            'SolverGetControl','SolverReleaseControl','ProfilerStart','DataManagerImport','DataManagerExport',
            'DataManagerFileNew','DataManagerFileOpen','DataManagerFileGetCurrent','DataImport220',
            'SecurityGetUsers','SecurityGetGroups','UserColorAdd','UserColorDelete','UserColorGetRGB',
            'UserColorModify','LicenseNumber','LicenseType','LicenseStartDate','LicenseExpirationDate',
            'LicenseMaintenanceExpirationDate','VARLicenseExpirationDate','AimmsRevisionString',
            'VARLicenseCreate','HistogramCreate','HistogramDelete','HistogramSetDomain',
            'HistogramAddObservation','HistogramGetFrequencies','HistogramGetBounds',
            'HistogramGetObservationCount','HistogramGetAverage','HistogramGetDeviation',
            'HistogramGetSkewness','HistogramGetKurtosis','DateDifferenceDays','DateDifferenceYearFraction',
            'PriceFractional','PriceDecimal','RateEffective','RateNominal','DepreciationLinearLife',
            'DepreciationLinearRate','DepreciationNonLinearSumOfYear','DepreciationNonLinearLife',
            'DepreciationNonLinearFactor','DepreciationNonLinearRate','DepreciationSum',
            'InvestmentConstantPresentValue','InvestmentConstantFutureValue',
            'InvestmentConstantPeriodicPayment','InvestmentConstantInterestPayment',
            'InvestmentConstantPrincipalPayment','InvestmentConstantCumulativePrincipalPayment',
            'InvestmentConstantCumulativeInterestPayment','InvestmentConstantNumberPeriods',
            'InvestmentConstantRateAll','InvestmentConstantRate','InvestmentVariablePresentValue',
            'InvestmentVariablePresentValueInperiodic','InvestmentSingleFutureValue',
            'InvestmentVariableInternalRateReturnAll','InvestmentVariableInternalRateReturn',
            'InvestmentVariableInternalRateReturnInperiodicAll','InvestmentVariableInternalRateReturnInperiodic',
            'InvestmentVariableInternalRateReturnModified','SecurityDiscountedPrice',
            'SecurityDiscountedRedemption','SecurityDiscountedYield','SecurityDiscountedRate',
            'TreasuryBillPrice','TreasuryBillYield','TreasuryBillBondEquivalent','SecurityMaturityPrice',
            'SecurityMaturityCouponRate','SecurityMaturityYield','SecurityMaturityAccruedInterest',
            'SecurityCouponNumber','SecurityCouponPreviousDate','SecurityCouponNextDate','SecurityCouponDays',
            'SecurityCouponDaysPreSettlement','SecurityCouponDaysPostSettlement','SecurityPeriodicPrice',
            'SecurityPeriodicRedemption','SecurityPeriodicCouponRate','SecurityPeriodicYieldAll',
            'SecurityPeriodicYield','SecurityPeriodicAccruedInterest','SecurityPeriodicDuration',
            'SecurityPeriodicDurationModified','Abs','AtomicUnit','Ceil','Character','CharacterNumber','Cube',
            'Degrees','Div','Exp','FileRead','Floor','Log','Log10','Mapval','Max','Min','Mod','Power',
            'Radians','Round','Sign','Sqr','Sqrt','StringCapitalize','StringLength','StringToLower',
            'StringToUnit','StringToUpper','SubString','Trunc','Binomial','NegativeBinomial','Poisson',
            'Geometric','HyperGeometric','Uniform','Normal','LogNormal','Triangular','Exponential','Weibull',
            'Beta','Gamma','Logistic','Pareto','ExtremeValue','Precision','Factorial','Combination',
            'Permutation','Errorf','Cos','Sin','Tan','ArcCos','ArcSin','ArcTan','Cosh','Sinh','Tanh',
            'ArcCosh','ArcSinh','ArcTanh'
            )
        ),
    'SYMBOLS' => array(
        0 => array(
            '(', ')', '[', ']', '{', '}',
            '%', '&', '|', '/',
            '<', '>', '>=' , '<=', ':=',
            '=', '-', '+', '*',
            '.', ','
            )
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0000FF;',
            2 => 'color: #000000; font-weight: bold;',
            3 => 'color: #404040;',
            4 => 'color: #990000; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #009900;'
            ),
        'STRINGS' => array(
            0 => 'color: #808080; font-style: italic ',
            'HARD' => 'color: #808080; font-style: italic'
            ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;',
            GESHI_NUMBER_OCT_PREFIX => 'color: #208080;',
            GESHI_NUMBER_HEX_PREFIX => 'color: #208080;',
            GESHI_NUMBER_FLT_SCI_ZERO => 'color:#800080;',
            ),
        'COMMENTS' => array(
            1 => 'color: #008000; font-style: italic;',
            'MULTI' => 'color: #008000; font-style: italic;'
            ),

        'METHODS' => array(
            1 => 'color: #004000;',
            2 => 'color: #004000;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #339933;',
            1 => 'color: #000000; font-weight: bold;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            0 => '',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => ''
            ),
        'ESCAPE_CHAR' => array()
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => false,
    'TAB_WIDTH' => 4
);
