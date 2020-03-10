import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { BuilderComponent } from './builder/builder.component';
import { MouseWheelDirective } from './mousewheel.directive';
import { DynamicItemsDirective } from './directives/dynamic-items.directive';
import { DragNDropComponent } from './drag-n-drop/drag-n-drop.component';
import { DynamicChildDirective } from './directives/dynamic-child.directive';
import { SendMessageComponent } from './components-preview/main-items/send-message/send-message.component';
import { PerformActionsComponent } from './components-preview/main-items/perform-actions/perform-actions.component';
import { StartAnotherFlowComponent } from './components-preview/main-items/start-another-flow/start-another-flow.component';
import { ConditionComponent } from './components-preview/main-items/condition/condition.component';
import { RandomizerComponent } from './components-preview/main-items/randomizer/randomizer.component';
import { SmartDelayComponent } from './components-preview/main-items/smart-delay/smart-delay.component';
import { SidebarComponent } from './sidebar/sidebar.component';
import { SendMessageItemsComponent } from './sidebar/send-message-items/send-message-items.component';
import { TextItemComponent } from './sidebar/send-message-items/text-item/text-item.component';
import { ImageItemComponent } from './sidebar/send-message-items/image-item/image-item.component';
import { CardItemComponent } from './sidebar/send-message-items/card-item/card-item.component';
import { AudioItemComponent } from './sidebar/send-message-items/audio-item/audio-item.component';
import { VideoItemComponent } from './sidebar/send-message-items/video-item/video-item.component';
import { FileItemComponent } from './sidebar/send-message-items/file-item/file-item.component';
import { DelayItemComponent } from './sidebar/send-message-items/delay-item/delay-item.component';
import { UserInputItemComponent } from './sidebar/send-message-items/user-input-item/user-input-item.component';
import { QuickReplyItemComponent } from './sidebar/send-message-items/quick-reply-item/quick-reply-item.component';
import { ButtonsItemComponent } from './sidebar/buttons-item/buttons-item.component';
import { PerformActionsItemsComponent } from './sidebar/perform-actions-items/perform-actions-items.component';
import { StartAnotherFlowItemsComponent } from './sidebar/start-another-flow-items/start-another-flow-items.component';
import { ConditionItemsComponent } from './sidebar/condition-items/condition-items.component';
import { RandomizerItemsComponent } from './sidebar/randomizer-items/randomizer-items.component';
import { SmartDelayItemsComponent } from './sidebar/smart-delay-items/smart-delay-items.component';
import { SendMessageChildsComponent } from './components-preview/child-items/send-message-childs/send-message-childs.component';
import { TextChildComponent } from './components-preview/child-items/send-message-childs/text-child/text-child.component';
import { ImageChildComponent } from './components-preview/child-items/send-message-childs/image-child/image-child.component';
import { CardChildComponent } from './components-preview/child-items/send-message-childs/card-child/card-child.component';
import { AudioChildComponent } from './components-preview/child-items/send-message-childs/audio-child/audio-child.component';
import { VideoChildComponent } from './components-preview/child-items/send-message-childs/video-child/video-child.component';
import { FileChildComponent } from './components-preview/child-items/send-message-childs/file-child/file-child.component';
import { DelayChildComponent } from './components-preview/child-items/send-message-childs/delay-child/delay-child.component';
import { UserInputChildComponent } from './components-preview/child-items/send-message-childs/user-input-child/user-input-child.component';
import {
  QuickReplyChildComponent
} from './components-preview/child-items/send-message-childs/quick-reply-child/quick-reply-child.component';
import { PerformActionsChildsComponent } from './components-preview/child-items/perform-actions-childs/perform-actions-childs.component';
import {
  StartAnotherFlowChildsComponent
} from './components-preview/child-items/start-another-flow-childs/start-another-flow-childs.component';
import { ConditionChildsComponent } from './components-preview/child-items/condition-childs/condition-childs.component';
import { RandomizerChildsComponent } from './components-preview/child-items/randomizer-childs/randomizer-childs.component';
import { SmartDelayChildsComponent } from './components-preview/child-items/smart-delay-childs/smart-delay-childs.component';
import { CreateMainItemComponent } from './components-preview/create-main-item/create-main-item.component';
import { DynamicSidebarItemsDirective } from './directives/dynamic-sidebar-items.directive';
import { ClickOutsideModule } from 'ng-click-outside';
import { DynamicChildSendMessageDirective } from './directives/dynamic-child-send-message.directive';
import { DefaultItemsComponent } from './sidebar/default-items/default-items.component';
import { FormsModule } from '@angular/forms';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { DragItemDirective } from './directives/drag-item.directive';
import { AutosizeModule } from 'ngx-autosize';
import { ButtonsComponent } from './components-preview/buttons/buttons.component';
import { TypeIconButtonPipe } from './pipes/type-icon-button.pipe';
import { NameNextButtonPipe } from './pipes/name-next-button.pipe';
import { ArrowComponent } from './arrow/arrow.component';
import { DragulaModule } from 'ng2-dragula';
import { NameNextStepPipe } from './pipes/name-next-step.pipe';
import { NamePerformActionsPipe } from './pipes/name-perform-actions.pipe';
import { IconPerformActionsPipe } from './pipes/icon-perform-actions.pipe';
import { NgSelectModule } from '@ng-select/ng-select';
import { TypeSmartDelayPipe } from './pipes/type-smart-delay.pipe';
import { SumRandomsValuePipe } from './pipes/sum-randoms-value.pipe';
import { SharedModule } from '../shared/shared.module';
import { AppRoutingModule } from '../../app-routing.module';
import { TypeNextStepPipe } from './pipes/type-next-step.pipe';
import { CheckLinksPerformActionsPipe } from './pipes/check-links-perform-actions.pipe';
import { PublishErrorsPipe } from './pipes/publish-errors.pipe';
import { PublishErrorsMainPipe } from './pipes/publish-errors-main.pipe';
import { FlowStatisticPipe } from './pipes/flow-statistic.pipe';
import { CheckUserInputPreviewPipe } from './pipes/check-user-input-preview.pipe';
import { AutosizeDirective } from './autosize.directive';

@NgModule({
  declarations: [
    BuilderComponent,
    MouseWheelDirective,
    DynamicItemsDirective,
    DynamicChildDirective,
    DragNDropComponent,
    SendMessageComponent,
    PerformActionsComponent,
    StartAnotherFlowComponent,
    ConditionComponent,
    RandomizerComponent,
    SmartDelayComponent,
    SidebarComponent,
    SendMessageItemsComponent,
    TextItemComponent,
    ImageItemComponent,
    CardItemComponent,
    AudioItemComponent,
    VideoItemComponent,
    FileItemComponent,
    DelayItemComponent,
    UserInputItemComponent,
    QuickReplyItemComponent,
    ButtonsItemComponent,
    PerformActionsItemsComponent,
    StartAnotherFlowItemsComponent,
    ConditionItemsComponent,
    RandomizerItemsComponent,
    SmartDelayItemsComponent,
    SendMessageChildsComponent,
    TextChildComponent,
    ImageChildComponent,
    CardChildComponent,
    AudioChildComponent,
    VideoChildComponent,
    FileChildComponent,
    DelayChildComponent,
    UserInputChildComponent,
    QuickReplyChildComponent,
    PerformActionsChildsComponent,
    StartAnotherFlowChildsComponent,
    ConditionChildsComponent,
    RandomizerChildsComponent,
    SmartDelayChildsComponent,
    CreateMainItemComponent,
    DynamicSidebarItemsDirective,
    DynamicChildSendMessageDirective,
    DefaultItemsComponent,
    DragItemDirective,
    ButtonsComponent,
    TypeIconButtonPipe,
    NameNextButtonPipe,
    ArrowComponent,
    NameNextStepPipe,
    NamePerformActionsPipe,
    IconPerformActionsPipe,
    TypeSmartDelayPipe,
    SumRandomsValuePipe,
    TypeNextStepPipe,
    CheckLinksPerformActionsPipe,
    PublishErrorsPipe,
    PublishErrorsMainPipe,
    FlowStatisticPipe,
    CheckUserInputPreviewPipe,
    AutosizeDirective
  ],
  imports: [
    CommonModule,
    ClickOutsideModule,
    AppRoutingModule,
    FormsModule,
    NgbModule,
    AutosizeModule,
    DragulaModule.forRoot(),
    NgSelectModule,
    SharedModule
  ],
  exports: [
    BuilderComponent,
    AutosizeDirective,
    MouseWheelDirective
  ],
  entryComponents: [
    SendMessageComponent,
    PerformActionsComponent,
    StartAnotherFlowComponent,
    ConditionComponent,
    RandomizerComponent,
    SmartDelayComponent,
    SendMessageChildsComponent,
    PerformActionsChildsComponent,
    StartAnotherFlowChildsComponent,
    ConditionChildsComponent,
    RandomizerChildsComponent,
    SmartDelayChildsComponent,
    TextChildComponent,
    ImageChildComponent,
    CardChildComponent,
    AudioChildComponent,
    VideoChildComponent,
    FileChildComponent,
    DelayChildComponent,
    UserInputChildComponent,
    SendMessageItemsComponent,
    PerformActionsItemsComponent,
    StartAnotherFlowItemsComponent,
    ConditionItemsComponent,
    RandomizerItemsComponent,
    SmartDelayItemsComponent,
  ]
})

export class MessagesBuilderModule { }
