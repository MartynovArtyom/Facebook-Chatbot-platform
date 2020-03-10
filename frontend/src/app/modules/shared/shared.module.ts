import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ClickOutsideModule } from 'ng-click-outside';
import { Ng5SliderModule } from 'ng5-slider';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { NgSelectModule } from '@ng-select/ng-select';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { AngularDraggableModule } from 'angular2-draggable';
import { ColorPickerModule } from 'ngx-color-picker';
import { PageNotFoundComponent } from '../../view/page-not-found/page-not-found.component';
import { CookieService } from 'ngx-cookie-service';
import { FlowTitlePipe } from '../../pipes/flow-title.pipe';
import {
  UserChoiseConditionOptionComponent
} from '../../view/user/partials/user-choise-condition-option/user-choise-condition-option.component';
import { UserConditionFilterComponent } from '../../view/user/partials/user-condition-filter/user-condition-filter.component';
import { ConditionNamePipe } from '../../pipes/condition-name.pipe';
import { CriteriaReplacePipe } from '../../pipes/criteria-replace.pipe';
import { ConditionValuePipe } from '../../pipes/condition-value.pipe';
import { FlowPopupComponent } from './flow-popup/flow-popup.component';
import { FoldersPopupComponent } from './flow-popup/folders-popup/folders-popup.component';
import { SidebarSelectedComponent } from './flow-popup/sidebar-selected/sidebar-selected.component';
import { PickerModule } from '@ctrl/ngx-emoji-mart';
import { CounterNotifyAdminsPipe } from '../messages-builder/pipes/counter-notify-admins.pipe';
import { CheckAdminsTeamPipe } from '../../pipes/check-admins-team.pipe';
import { CheckParentsMainMenuPipe } from '../../pipes/check-parents-main-menu.pipe';
import { ValidMainMenuPipe } from '../../pipes/valid-main-menu.pipe';
import { ValidActionsMainMenuPipe } from '../../pipes/valid-actions-main-menu.pipe';
import { BroadcastTypePipe } from '../../pipes/broadcast-type.pipe';
import { MobileFlowPreviewComponent } from './mobile-flow-preview/mobile-flow-preview.component';
import { NoSanitizePipe } from '../../pipes/no-sanitize.pipe';
import { OwlDateTimeModule, OwlNativeDateTimeModule } from 'ng-pick-datetime';
import { DateUpdatedMessagePipe } from '../../pipes/date-updated-message.pipe';
import { ResponderComponent } from './responder/responder.component';
import { RouterModule } from '@angular/router';
import { PreviewAcceptComponent } from './preview-accept/preview-accept.component';
import { OpenMessengerToastrComponent } from './open-messenger-toastr/open-messenger-toastr.component';
import { UserInputDateComponent } from './user-input-date/user-input-date.component';
import { UserInputDatetimeComponent } from './user-input-datetime/user-input-datetime.component';
import { SentProcentsPipe } from '../../pipes/sent-procents.pipe';
import { EmojiPickerComponent } from './emoji-picker/emoji-picker.component';
import { CheckTabClassPipe } from '../../pipes/check-tab-class.pipe';
import { KampaignPopupComponent } from './kampaign-popup/kampaign-popup.component';
import {
  KeywordsActionsComponent
} from '../../view/user/partials/user-automation/user-automation-keywords/keywords-actions/keywords-actions.component';
import { StartingStepFlowPopupComponent } from './starting-step-flow-popup/starting-step-flow-popup.component';
import { SelectedBarStartStepComponent } from './starting-step-flow-popup/selected-bar-start-step/selected-bar-start-step.component';
import { UpdatePlanComponent } from './update-plan/update-plan.component';
import { UrlTypePipe } from '../../pipes/url-type.pipe';
import { DateCreatedPostPipe } from '../../pipes/date-created-post.pipe';

@NgModule({
  imports: [
    CommonModule,
    AngularDraggableModule,
    FormsModule,
    NgSelectModule,
    NgbModule,
    Ng5SliderModule,
    ClickOutsideModule,
    PickerModule,
    ColorPickerModule,
    OwlDateTimeModule,
    RouterModule,
    OwlNativeDateTimeModule
  ],
  declarations: [
    PageNotFoundComponent,
    FlowTitlePipe,
    ConditionNamePipe,
    CriteriaReplacePipe,
    ConditionValuePipe,
    CounterNotifyAdminsPipe,
    CheckParentsMainMenuPipe,
    CheckAdminsTeamPipe,
    ValidMainMenuPipe,
    UserChoiseConditionOptionComponent,
    UserConditionFilterComponent,
    FlowPopupComponent,
    FoldersPopupComponent,
    SidebarSelectedComponent,
    ValidActionsMainMenuPipe,
    BroadcastTypePipe,
    MobileFlowPreviewComponent,
    NoSanitizePipe,
    DateUpdatedMessagePipe,
    ResponderComponent,
    PreviewAcceptComponent,
    OpenMessengerToastrComponent,
    UserInputDateComponent,
    UserInputDatetimeComponent,
    SentProcentsPipe,
    EmojiPickerComponent,
    CheckTabClassPipe,
    KampaignPopupComponent,
    KeywordsActionsComponent,
    StartingStepFlowPopupComponent,
    SelectedBarStartStepComponent,
    UpdatePlanComponent,
    UrlTypePipe,
    DateCreatedPostPipe
  ],
  entryComponents: [
    FlowPopupComponent,
    FoldersPopupComponent,
    SidebarSelectedComponent,
    MobileFlowPreviewComponent,
  ],
  providers: [
    CookieService
  ],
  exports: [
    PageNotFoundComponent,
    AngularDraggableModule,
    FormsModule,
    ReactiveFormsModule,
    NgSelectModule,
    NgbModule,
    Ng5SliderModule,
    ClickOutsideModule,
    ColorPickerModule,
    FlowTitlePipe,
    ConditionNamePipe,
    CriteriaReplacePipe,
    ConditionValuePipe,
    UserChoiseConditionOptionComponent,
    UserConditionFilterComponent,
    FlowPopupComponent,
    FoldersPopupComponent,
    CounterNotifyAdminsPipe,
    CheckParentsMainMenuPipe,
    ValidMainMenuPipe,
    CheckAdminsTeamPipe,
    PickerModule,
    SidebarSelectedComponent,
    ValidActionsMainMenuPipe,
    BroadcastTypePipe,
    MobileFlowPreviewComponent,
    NoSanitizePipe,
    OwlDateTimeModule,
    OwlNativeDateTimeModule,
    DateUpdatedMessagePipe,
    PreviewAcceptComponent,
    OpenMessengerToastrComponent,
    UserInputDateComponent,
    UserInputDatetimeComponent,
    SentProcentsPipe,
    CheckTabClassPipe,
    EmojiPickerComponent,
    KampaignPopupComponent,
    KeywordsActionsComponent,
    StartingStepFlowPopupComponent,
    SelectedBarStartStepComponent,
    UpdatePlanComponent,
    UrlTypePipe,
    DateCreatedPostPipe
  ]
})
export class SharedModule { }
