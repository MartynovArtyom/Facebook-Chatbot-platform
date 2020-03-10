import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { PageNotFoundComponent } from '../page-not-found/page-not-found.component';
import { UserSettingsComponent } from './partials/user-settings/user-settings.component';
import { UserContentFlowEditComponent } from './partials/user-content/user-content-flow-edit/user-content-flow-edit.component';
import { CheckIdGuard } from '../../guards/check-id.guard';
import { UserContentFlowPreviewComponent } from './partials/user-content/user-content-flow-preview/user-content-flow-preview.component';
import {
  UserAutomationWelcomeEditComponent
} from './partials/user-automation/user-automation-welcome-edit/user-automation-welcome-edit.component';
import { UserAutomationWelcomeComponent } from './partials/user-automation/user-automation-welcome/user-automation-welcome.component';
import {
  UserAutomationDefaultEditComponent
} from './partials/user-automation/user-automation-default-edit/user-automation-default-edit.component';
import { UserAutomationDefaultComponent } from './partials/user-automation/user-automation-default/user-automation-default.component';
import { UserAutomationMenuComponent } from './partials/user-automation/user-automation-menu/user-automation-menu.component';
import { UserBroadcastsHistoryComponent } from './partials/user-broadcasts/user-broadcasts-history/user-broadcasts-history.component';
import { UserBroadcastsScheduleComponent } from './partials/user-broadcasts/user-broadcasts-schedule/user-broadcasts-schedule.component';
import {
  UserBroadcastsDraftEditComponent
} from './partials/user-broadcasts/user-broadcasts-draft-edit/user-broadcasts-draft-edit.component';
import { UserBroadcastsDraftComponent } from './partials/user-broadcasts/user-broadcasts-draft/user-broadcasts-draft.component';
import { UserBroadcastsComponent } from './partials/user-broadcasts/user-broadcasts.component';
import { UserLiveChatComponent } from './partials/user-live-chat/user-live-chat.component';
import { UserAudienceComponent } from './partials/user-audience/user-audience.component';
import { UserDashboardComponent } from './partials/user-dashboard/user-dashboard.component';
import { UserComponent } from './user.component';
import { EditMenuComponent } from './partials/user-automation/user-automation-menu/edit-menu/edit-menu.component';
import {
  AutomationMenuEditFlowComponent
} from './partials/user-automation/user-automation-menu/automation-menu-edit-flow/automation-menu-edit-flow.component';
import {
  UserBroadcastsHistoryPreviewComponent
} from './partials/user-broadcasts/user-broadcasts-history-preview/user-broadcasts-history-preview.component';
import {
  UserBroadcastsSchedulePreviewComponent
} from './partials/user-broadcasts/user-broadcasts-schedule-preview/user-broadcasts-schedule-preview.component';
import { ResponderComponent } from '../../modules/shared/responder/responder.component';
import { UserCompanyManagerComponent } from './partials/user-company-manager/user-company-manager.component';
import { CheckLivechatRoleGuard } from '../../guards/check-livechat-role.guard';
import { CheckEditorRoleGuard } from '../../guards/check-editor-role.guard';
import { CheckViewerRoleGuard } from '../../guards/check-viewer-role.guard';
import {UserGrowthToolsComponent} from './partials/user-growth-tools/user-growth-tools.component';

const routes: Routes = [
  { path: '', redirectTo: ':id', pathMatch: 'full' },
  { path: ':id', component: UserComponent, children: [
    {
      path: '',
      redirectTo: 'dashboard',
      pathMatch: 'full'
    },
    {
      path: 'dashboard',
      canActivate: [ CheckIdGuard ],
      component: UserDashboardComponent
    },
    {
      path: 'test',
      canActivate: [ CheckIdGuard ],
      component: UserGrowthToolsComponent
    },
    {
      path: 'company-manager',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserCompanyManagerComponent
    },
    {
      path: 'audience',
      canActivate: [ CheckIdGuard ],
      component: UserAudienceComponent
    },
    {
      path: 'chat',
      canActivate: [ CheckIdGuard ],
      component: UserLiveChatComponent
    },
    {
      path: 'broadcasts',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserBroadcastsComponent
    },
    {
      path: 'broadcasts/drafts',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserBroadcastsDraftComponent
    },
    {
      path: 'broadcasts/drafts/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: UserBroadcastsDraftEditComponent
    },
    {
      path: 'broadcasts/scheduled',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserBroadcastsScheduleComponent
    },
    {
      path: 'broadcasts/scheduled/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserBroadcastsSchedulePreviewComponent
    },
    {
      path: 'broadcasts/history',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserBroadcastsHistoryComponent
    },
    {
      path: 'broadcasts/history/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserBroadcastsHistoryPreviewComponent
    },
    {
      path: 'automation/menu',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserAutomationMenuComponent
    },
    {
      path: 'automation/menu/edit',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: EditMenuComponent
    },
    {
      path: 'automation/menu/edit/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: AutomationMenuEditFlowComponent
    },
    { path: 'automation/default',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserAutomationDefaultComponent
    },
    {
      path: 'automation/default/:id/responses/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: ResponderComponent
    },
    {
      path: 'automation/default/edit',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: UserAutomationDefaultEditComponent
    },
    {
      path: 'automation/welcome',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: UserAutomationWelcomeComponent
    },
    {
      path: 'automation/welcome/:id/responses/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard ],
      component: ResponderComponent
    },
    {
      path: 'automation/welcome/edit',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: UserAutomationWelcomeEditComponent
    },
    {
      path: 'content/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: UserContentFlowPreviewComponent
    },
    {
      path: 'content/:id/responses/:id',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: ResponderComponent
    },
    {
      path: 'content/:id/edit',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckViewerRoleGuard ],
      component: UserContentFlowEditComponent
    },
    {
      path: 'settings',
      canActivate: [ CheckIdGuard, CheckLivechatRoleGuard, CheckEditorRoleGuard, CheckViewerRoleGuard ],
      component: UserSettingsComponent
    },
    {
      path: '**',
      canActivate: [ CheckIdGuard ],
      component: PageNotFoundComponent
    }
  ]}
];

@NgModule({
  imports: [
    RouterModule.forChild(routes)
  ],
  exports: [
    RouterModule
  ]
})
export class UserRoutingModule { }
