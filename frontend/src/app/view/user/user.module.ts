import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { UserRoutingModule } from './user-routing.module';
import { UserSubscriberProfileComponent } from './partials/user-subscriber-profile/user-subscriber-profile.component';
import { UserAudienceComponent } from './partials/user-audience/user-audience.component';
import { UserAutomationDefaultComponent } from './partials/user-automation/user-automation-default/user-automation-default.component';
import {
  UserAutomationDefaultEditComponent
} from './partials/user-automation/user-automation-default-edit/user-automation-default-edit.component';
import { UserAutomationKeywordsComponent } from './partials/user-automation/user-automation-keywords/user-automation-keywords.component';
import { UserAutomationMenuComponent } from './partials/user-automation/user-automation-menu/user-automation-menu.component';
import { UserAutomationWelcomeComponent } from './partials/user-automation/user-automation-welcome/user-automation-welcome.component';
import {
  UserAutomationWelcomeEditComponent
} from './partials/user-automation/user-automation-welcome-edit/user-automation-welcome-edit.component';
import { UserAutopostingComponent } from './partials/user-autoposting/user-autoposting.component';
import { UserAutopostingChannelComponent } from './partials/user-autoposting/user-autoposting-channel/user-autoposting-channel.component';
import { UserBroadcastsComponent } from './partials/user-broadcasts/user-broadcasts.component';
import { UserBroadcastsDraftComponent } from './partials/user-broadcasts/user-broadcasts-draft/user-broadcasts-draft.component';
import {
  UserBroadcastsDraftEditComponent
} from './partials/user-broadcasts/user-broadcasts-draft-edit/user-broadcasts-draft-edit.component';
import { UserBroadcastsHistoryComponent } from './partials/user-broadcasts/user-broadcasts-history/user-broadcasts-history.component';
import { UserBroadcastsScheduleComponent } from './partials/user-broadcasts/user-broadcasts-schedule/user-broadcasts-schedule.component';
import { UserBulkActionsComponent } from './partials/user-bulk-actions/user-bulk-actions.component';
import { UserContentComponent } from './partials/user-content/user-content.component';
import { UserContentFlowEditComponent } from './partials/user-content/user-content-flow-edit/user-content-flow-edit.component';
import { UserContentFlowPreviewComponent } from './partials/user-content/user-content-flow-preview/user-content-flow-preview.component';
import { UserDashboardComponent } from './partials/user-dashboard/user-dashboard.component';
import { UserFlowComponent } from './partials/user-flow/user-flow.component';
import { UserGrowthToolsComponent } from './partials/user-growth-tools/user-growth-tools.component';
import { UserGrowthToolsEditComponent } from './partials/user-growth-tools/user-growth-tools-edit/user-growth-tools-edit.component';
import { UserLiveChatComponent } from './partials/user-live-chat/user-live-chat.component';
import { UserSettingsComponent } from './partials/user-settings/user-settings.component';
import { UserSettingsAdminsComponent } from './partials/user-settings/user-settings-admins/user-settings-admins.component';
import {
  UserSettingsCustomFieldsComponent
} from './partials/user-settings/user-settings-custom-fields/user-settings-custom-fields.component';
import { UserSettingsGeneralComponent } from './partials/user-settings/user-settings-general/user-settings-general.component';
import {
  UserSettingsNotificationComponent
} from './partials/user-settings/user-settings-notification/user-settings-notification.component';
import { UserSettingsPopupComponent } from './partials/user-settings/user-settings-popup/user-settings-popup.component';
import { UserSettingsTagsComponent } from './partials/user-settings/user-settings-tags/user-settings-tags.component';
import { UserSidebarComponent } from './partials/user-sidebar/user-sidebar.component';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { SharedModule } from '../../modules/shared/shared.module';
import { DragulaModule } from 'ng2-dragula';
import { KonvaModule } from 'ng2-konva';
import { NouisliderModule } from 'ng2-nouislider';
import { AutosizeModule } from 'ngx-autosize';
import { UserComponent } from './user.component';
import { SafePipe } from '../../pipes/safe.pipe';
import { TypeWidgetPipe } from '../../pipes/type-widget.pipe';
import { ChartsModule } from 'ng2-charts';
import { UserFlowFoldersComponent } from './partials/user-flow/user-flow-folders/user-flow-folders.component';
import { MessagesBuilderModule } from '../../modules/messages-builder/messages-builder.module';
import {
  UserAutomationKeywordsPreviewComponent
} from './partials/user-automation/user-automation-keywords/user-automation-keywords-preview/user-automation-keywords-preview.component';
import {
  UserAutomationKeywordsEditComponent
} from './partials/user-automation/user-automation-keywords/user-automation-keywords-edit/user-automation-keywords-edit.component';
import { UserAutomationSequencesComponent } from './partials/user-automation/user-automation-sequences/user-automation-sequences.component';
import {
  UserAutomationSequencesEditComponent
} from './partials/user-automation/user-automation-sequences/user-automation-sequences-edit/user-automation-sequences-edit.component';
import {
  SequenceFlowPreviewComponent
} from './partials/user-automation/user-automation-sequences/sequence-flow-preview/sequence-flow-preview.component';
import {
  SequenceFlowEditComponent
} from './partials/user-automation/user-automation-sequences/sequence-flow-edit/sequence-flow-edit.component';
import { EditMenuComponent } from './partials/user-automation/user-automation-menu/edit-menu/edit-menu.component';
import { MainMenuPopoverComponent } from './partials/user-automation/user-automation-menu/main-menu-popover/main-menu-popover.component';
import {
  AutomationMenuEditFlowComponent
} from './partials/user-automation/user-automation-menu/automation-menu-edit-flow/automation-menu-edit-flow.component';
import {
  UserBroadcastsSchedulePreviewComponent
} from './partials/user-broadcasts/user-broadcasts-schedule-preview/user-broadcasts-schedule-preview.component';
import {
  UserBroadcastsHistoryPreviewComponent
} from './partials/user-broadcasts/user-broadcasts-history-preview/user-broadcasts-history-preview.component';
import { OwlDateTimeModule, OwlNativeDateTimeModule } from 'ng-pick-datetime';
import { BarWidgetComponent } from './partials/user-growth-tools/widgets/bar-widget/bar-widget.component';
import { SlideInWidgetComponent } from './partials/user-growth-tools/widgets/slide-in-widget/slide-in-widget.component';
import { ModalWidgetComponent } from './partials/user-growth-tools/widgets/modal-widget/modal-widget.component';
import { PageTakeoverWidgetComponent } from './partials/user-growth-tools/widgets/page-takeover-widget/page-takeover-widget.component';
import { ButtonWidgetComponent } from './partials/user-growth-tools/widgets/button-widget/button-widget.component';
import { BoxWidgetComponent } from './partials/user-growth-tools/widgets/box-widget/box-widget.component';
import { ChatWidgetComponent } from './partials/user-growth-tools/widgets/chat-widget/chat-widget.component';
import {
  UserGrowthToolsFlowPreviewComponent
} from './partials/user-growth-tools/user-growth-tools-flow-preview/user-growth-tools-flow-preview.component';
import {
  UserGrowthToolsFlowEditComponent
} from './partials/user-growth-tools/user-growth-tools-flow-edit/user-growth-tools-flow-edit.component';
import { BrowserModule } from '@angular/platform-browser';
import { NgxInfiniteScrollerModule } from 'ngx-infinite-scroller';
import { UserSettingsZapierComponent } from './partials/user-settings/user-settings-zapier/user-settings-zapier.component';
import { AutoSizeInputModule } from 'ngx-autosize-input';
import { UserCompanyManagerComponent } from './partials/user-company-manager/user-company-manager.component';
import { CompanyComponent } from './partials/user-company-manager/manager-components/company/company.component';
import { AutoresponderComponent } from './partials/user-company-manager/manager-components/autoresponder/autoresponder.component';
import { KeywordsComponent } from './partials/user-company-manager/manager-components/keywords/keywords.component';
import { FlowsComponent } from './partials/user-company-manager/manager-components/flows/flows.component';
import { TabsFirstLevelComponent } from './partials/user-company-manager/tabs-first-level/tabs-first-level.component';
import { TabsSecondLevelComponent } from './partials/user-company-manager/tabs-second-level/tabs-second-level.component';
import { GrowthToolsComponent } from './partials/user-company-manager/tabs-components/growth-tools/growth-tools.component';
import { SequencesComponent } from './partials/user-company-manager/tabs-components/sequences/sequences.component';
import { CampaignComponent } from './partials/user-company-manager/manager-components/campaign/campaign.component';
import { FlowComponent } from './partials/user-company-manager/tabs-components/flow/flow.component';
import { FlowEditComponent } from './partials/user-company-manager/tabs-components/flow/flow-edit/flow-edit.component';
import { FlowResponsesComponent } from './partials/user-company-manager/tabs-components/flow/flow-responses/flow-responses.component';
import { SearchCompanyComponent } from './partials/user-company-manager/manager-components/search-company/search-company.component';
import { WootricSurveyDirective } from './wootric-survey.directive';
import { UserSettingsContractsComponent } from './partials/user-settings/user-settings-contracts/user-settings-contracts.component';
import { FbPostsComponent } from './partials/user-company-manager/tabs-components/growth-tools/fb-posts/fb-posts.component';

@NgModule({
  declarations: [
    UserComponent,
    UserSubscriberProfileComponent,
    UserAudienceComponent,
    UserAutomationDefaultComponent,
    UserAutomationDefaultEditComponent,
    UserAutomationKeywordsComponent,
    UserAutomationMenuComponent,
    UserAutomationWelcomeComponent,
    UserAutomationWelcomeEditComponent,
    UserAutopostingComponent,
    UserAutopostingChannelComponent,
    UserBroadcastsComponent,
    UserBroadcastsDraftComponent,
    UserBroadcastsDraftEditComponent,
    UserBroadcastsHistoryComponent,
    UserBroadcastsScheduleComponent,
    UserBulkActionsComponent,
    UserContentComponent,
    UserContentFlowEditComponent,
    UserContentFlowPreviewComponent,
    UserDashboardComponent,
    UserFlowComponent,
    UserGrowthToolsComponent,
    UserGrowthToolsEditComponent,
    UserLiveChatComponent,
    UserSettingsComponent,
    UserSettingsAdminsComponent,
    UserSettingsCustomFieldsComponent,
    UserSettingsGeneralComponent,
    UserSettingsNotificationComponent,
    UserSettingsPopupComponent,
    UserSettingsTagsComponent,
    UserSidebarComponent,
    SafePipe,
    TypeWidgetPipe,
    UserFlowFoldersComponent,
    UserAutomationKeywordsPreviewComponent,
    UserAutomationKeywordsEditComponent,
    UserAutomationSequencesComponent,
    UserAutomationSequencesEditComponent,
    SequenceFlowPreviewComponent,
    SequenceFlowEditComponent,
    EditMenuComponent,
    MainMenuPopoverComponent,
    AutomationMenuEditFlowComponent,
    UserBroadcastsSchedulePreviewComponent,
    UserBroadcastsHistoryPreviewComponent,
    BarWidgetComponent,
    SlideInWidgetComponent,
    ModalWidgetComponent,
    PageTakeoverWidgetComponent,
    ButtonWidgetComponent,
    BoxWidgetComponent,
    ChatWidgetComponent,
    UserGrowthToolsFlowPreviewComponent,
    UserGrowthToolsFlowEditComponent,
    UserSettingsZapierComponent,
    UserCompanyManagerComponent,
    CompanyComponent,
    AutoresponderComponent,
    KeywordsComponent,
    FlowsComponent,
    TabsFirstLevelComponent,
    TabsSecondLevelComponent,
    GrowthToolsComponent,
    SequencesComponent,
    CampaignComponent,
    FlowComponent,
    FlowEditComponent,
    FlowResponsesComponent,
    SearchCompanyComponent,
    WootricSurveyDirective,
    UserSettingsContractsComponent,
    FbPostsComponent
  ],
  imports: [
    CommonModule,
    UserRoutingModule,
    SharedModule,
    BrowserModule,
    BrowserAnimationsModule,
    KonvaModule,
    NouisliderModule,
    AutosizeModule,
    ChartsModule,
    DragulaModule.forRoot(),
    AutoSizeInputModule,
    MessagesBuilderModule,
    OwlDateTimeModule,
    OwlNativeDateTimeModule,
    NgxInfiniteScrollerModule
  ],
  entryComponents: [
  ]
})
export class UserModule { }
